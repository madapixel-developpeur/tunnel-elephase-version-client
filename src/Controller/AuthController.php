<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ReinitPasswordFormType;
use App\Form\UserFormType;
use App\Service\AuthService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Validator\Constraints\NotBlank;

#[Route('/auth')]
class AuthController extends AbstractController
{
    private $authService;
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    #[Route(path: '/login', name: 'app_auth_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername, 
            'error' => $error
        ]);
    }

    #[Route(path: '/signup', name: 'app_auth_signup')]
    public function signup(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserFormType::class, $user, ['signup' => true]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            try{
                
                $user = $this->authService->saveClient($user);
                $request->getSession()->set('userId', $user->getId());
                return $this->redirectToRoute('app_auth_check_account');
                
            } catch(Exception $ex){
                $error = $ex->getMessage();
                $this->addFlash('danger', $error);
            }

        }

        return $this->render('auth/signup.html.twig',[
            'form' => $form->createView()
        ]);
    
    }

    
    #[Route(path: '/checkAccount', name: 'app_auth_check_account')]
    public function checkAccount(Request $request, EventDispatcherInterface $eventDispatcher, TokenStorageInterface $tokenStorage): Response
    {
        $userId = $request->getSession()->get('userId', null);
        if(!$userId){
            return $this->redirectToRoute('app_auth_signup');
        }
        
        $form = $this->createFormBuilder()
            ->add('verifCode', TextType::class, [
                "label"=>"Code de vérification", 
                "trim" => true, 
                "required" => true,
                "constraints" => [
                    new NotBlank(["message" => "Code de vérification obligatoire"])
                ]
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            try{
                $verifCode = $form->get('verifCode')->getData();
                $user = $this->authService->validateAccount($userId, $verifCode);
                $request->getSession()->remove('userId');
                
                $token = new UsernamePasswordToken($user, "main", $user->getRoles());
                $tokenStorage->setToken($token);
                $event = new InteractiveLoginEvent($request, $token);
                $eventDispatcher->dispatch($event, "security.interactive_login");
                return $this->redirectToRoute('app_blog_index');
            } catch(Exception $ex){
                $error = $ex->getMessage();
                $this->addFlash('danger', $error);
            }

        }

        return $this->render('auth/validate_account.html.twig',[
            'form' => $form->createView()
        ]);
    
    }

    #[Route(path: '/resendVerifCode', name: 'app_auth_resend_verifcode')]
    public function resendVerifCode(Request $request): Response
    {
        $userId = $request->getSession()->get('userId', null);
        if(!$userId){
            return $this->redirectToRoute('app_auth_signup');
        }
        try{
            $this->authService->sendVerifCode($userId);
            $this->addFlash('success', 'Code envoyé');
        } catch(Exception $ex){
            $error = $ex->getMessage();
            $this->addFlash('danger', $error);
        }
        return $this->redirectToRoute('app_auth_check_account');
    }

    #[Route(path: '/forgotPwd', name: 'app_auth_forgot_pwd')]
    public function forgotPwd(Request $request): Response
    {
    
        $form = $this->createFormBuilder()
            ->add('mail', EmailType::class, [
                "label"=>"Adresse email", 
                "trim" => true, 
                "required" => true,
                "constraints" => [
                    new NotBlank(["message" => "Adresse email obligatoire"])
                ]
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            try{
                $mail = $form->get('mail')->getData();
                $user = $this->authService->forgotPassword($mail);
                $request->getSession()->set('mail', $user->getMail());
                return $this->redirectToRoute('app_auth_reinit_pwd');
            } catch(Exception $ex){
                $error = $ex->getMessage();
                $this->addFlash('danger', $error);
            }

        }

        return $this->render('auth/forgotpwd.html.twig',[
            'form' => $form->createView(),
        ]);
    
    }

    #[Route(path: '/reinitPwd', name: 'app_auth_reinit_pwd')]
    public function reinitPwd(Request $request): Response
    {
        $mail = $request->getSession()->get('mail', null);
        if($mail == null){
            return $this->redirectToRoute('app_auth_forgot_pwd');
        }
        
        $form = $this->createForm(ReinitPasswordFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            try{
                $verifCode = $form->get('verifCode')->getData();
                $newPassword = $form->get('newPassword')->getData();
                $this->authService->reinitPassword($mail, $verifCode, $newPassword);
                $this->addFlash('success', "Mot de passe réinitialisé, essayer de vous connecter maintenant");
                return $this->redirectToRoute('app_auth_login');
            } catch(Exception $ex){
                $error = $ex->getMessage();
                $this->addFlash('danger', $error);
            }

        }

        return $this->render('auth/reinitpwd.html.twig',[
            'form' => $form->createView(),
        ]);
    
    }

    #[Route('/logout', name: 'app_auth_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
