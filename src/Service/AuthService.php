<?php

namespace App\Service;

use App\Entity\AccountValidation;
use App\Entity\ForgotPassword;
use App\Entity\User;
use App\Repository\AccountValidationRepository;
use App\Repository\ForgotPasswordRepository;
use App\Repository\UserRepository;
use App\Util\Status;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthService 
{
    private $entityManager;
    private $userRepository;
    private $mailService;
    private $forgotPasswordRepository;
    private $passwordHasher;
    private $accountValidationRepository;

    public function __construct(
        EntityManagerInterface $entityManager, 
        UserRepository $userRepository, 
        ForgotPasswordRepository $forgotPasswordRepository,  
        UserPasswordHasherInterface $passwordHasher, 
        MailService $mailService,
        AccountValidationRepository $accountValidationRepository
        )
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->forgotPasswordRepository = $forgotPasswordRepository;
        $this->passwordHasher = $passwordHasher;
        $this->mailService = $mailService;
        $this->accountValidationRepository = $accountValidationRepository;
    }

    
    public function findUserByMail($mail): User{
        $user = $this->userRepository->findOneBy(['mail' => strtolower($mail)]);
        if($user == null) 
            throw new Exception("Aucun compte n'est associé à cette adresse email");
        return $user;    
    }

    public function forgotPassword($mail): User
    {
        $user = $this->findUserByMail($mail);
        $code =  $this->generateRandomNDigits(8);   
        $dateExpiration = new DateTime();
        $dateExpiration->add(new DateInterval('PT1H'));
        
        $forgotPw = new ForgotPassword();
        $forgotPw->setUser($user);
        $forgotPw->setVerifCode(sha1($code));
        $forgotPw->setDateExpiration($dateExpiration);
        $forgotPw->setStatus(Status::VALID);
        
        $this->entityManager->persist($forgotPw);
        $this->entityManager->flush();

        $mail = [
            'subject' => 'Réinitilisation du mot de passe',
            'to' => $user->getMail(),
            'body' => $this->mailService->renderTwig('mail/auth/reinit_password.html.twig', [
                'code' => $code,
                'dateExpiration' => $dateExpiration
            ])
        ];
    
        $this->mailService->sendMail($mail);
        return $user;
    }
    

    public function saveClient(User $user): User
    {
        try{
            $this->entityManager->beginTransaction();
            $password = $this->passwordHasher->hashPassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->setDateInscription(new DateTime());
            $user->setRoles(["ROLE_CLIENT"]);
            $user->setStatus(User::STATUS_CREATED);
            $user->setMail(strtolower($user->getMail()));
            $this->entityManager->persist($user);

            
            $this->sendVerifCode(0, false, $user);
           
            $this->entityManager->flush();

            $this->entityManager->commit();
            return $user;
        } 
        catch(\Exception $ex){
            if($this->entityManager->getConnection()->isTransactionActive()) {
                $this->entityManager->rollback();
            }
            throw $ex;
        }
        finally {
            $this->entityManager->clear();
        }
    }

    public function sendVerifCode(int $userId, $flush = true, $user = null){
        if(!$user){
            $user = $this->findCreatedUser($userId);
        }
        $code =  $this->generateRandomNDigits(8);   
        $dateExpiration = new DateTime();
        $dateExpiration->add(new DateInterval('PT1H'));
    
        $accountValidation = new AccountValidation();
        $accountValidation->setUser($user);
        $accountValidation->setVerifCode(strtolower(sha1($code)));
        $accountValidation->setDateExpiration($dateExpiration);
        $accountValidation->setStatus(Status::VALID);
    
        $this->entityManager->persist($accountValidation);
        

        $mail = [
            'subject' => 'Validation du nouveau compte',
            'to' => $user->getMail(),
            'body' => $this->mailService->renderTwig('mail/auth/account_validation.html.twig', [
                'code' => $code,
                'dateExpiration' => $dateExpiration
            ])
        ];
    
        $this->mailService->sendMail($mail);
        if($flush) $this->entityManager->flush();

    }
    public function findCreatedUser(int $userId): ?User{
        $user = $this->userRepository->findOneBy(['id' => $userId, 'status' => User::STATUS_CREATED]);
        if($user == null)
            throw new Exception("Utilisateur introuvable");
        return $user;    
    }
    public function validateAccount(int $userId, $verifCode)
    {
        try{
            $this->entityManager->beginTransaction();
            $user = $this->findCreatedUser($userId);
            $accountValidation = $this->accountValidationRepository->getValidAccountValidation($user, $verifCode);
            if($accountValidation == null) 
                throw new Exception("Code de vérification invalide");

            $user->setStatus(User::STATUS_VALID);    
            $accountValidation->setStatus(Status::INVALID);
            
            $this->entityManager->persist($accountValidation);
            $this->entityManager->flush();

            $this->entityManager->commit();
            return $user;
        } 
        catch(\Exception $ex){
            if($this->entityManager->getConnection()->isTransactionActive()) {
                $this->entityManager->rollback();
            }
            throw $ex;
        }
        finally {
            $this->entityManager->clear();
        }    
    }

    
    public function reinitPassword($mail, $verifCode, $newPassword){

        try{
            $this->entityManager->beginTransaction();
            $user = $this->findUserByMail($mail);
            $forgotPw = $this->forgotPasswordRepository->getValidForgotPwd($user, $verifCode);
            if($forgotPw == null) 
                throw new Exception("Code de vérification invalide");

            $user->setPassword($this->passwordHasher->hashPassword($user, $newPassword));
            $forgotPw->setStatus(Status::INVALID);
            
            $this->entityManager->persist($user);
            $this->entityManager->persist($forgotPw);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } 
        catch(\Exception $ex){
            if($this->entityManager->getConnection()->isTransactionActive()) {
                $this->entityManager->rollback();
            }
            throw $ex;
        }
        finally {
            $this->entityManager->clear();
        }    
    }
    

    public function generateRandomNDigits(int $n){
        $code = "";
        for($i=0; $i<$n; $i++){
            $code .= rand(0, 9);
        }
        return $code;
    }

    
    public function changePassword(User $user, string $currentPassword, string $newPassword)
    {
        if(!$this->passwordHasher->isPasswordValid($user, $currentPassword)){
            throw new Exception("Mot de passe actuel invalide");
        }

        $password = $this->passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($password);
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
    
}
