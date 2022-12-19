<?php
namespace App\Service;

use App\Entity\OrderCoffret;
use App\Repository\CoffretRepository;
use App\Repository\OrderCoffretRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Nucleos\DompdfBundle\Wrapper\DompdfWrapperInterface;

class OrderCoffretService
{
    private $entityManager;
    private $stripeService;
    private $coffretRepository;
    private OrderCoffretRepository $orderCoffretRepository;
    private $mailService;
    private $wrapper;
    private $fileHandler;
    private $configService;
    private $repoUser;

    public function __construct(EntityManagerInterface $entityManager, StripeService $stripeService, CoffretRepository $coffretRepository, OrderCoffretRepository $orderCoffretRepository, MailService $mailService, DompdfWrapperInterface $wrapper, FileHandler $fileHandler, ConfigService $configService, UserRepository $repoUser)
    {
        $this->entityManager = $entityManager;
        $this->stripeService = $stripeService;
        $this->coffretRepository = $coffretRepository;

        $this->orderCoffretRepository = $orderCoffretRepository;
        $this->mailService = $mailService;
        $this->wrapper = $wrapper;
        $this->fileHandler = $fileHandler;
        $this->configService = $configService;
        $this->repoUser = $repoUser;
    }

    

    // public function saveOrder(OrderCoffret $order, string $stripeToken): ?OrderCoffret{
    public function saveOrder(OrderCoffret $order): ?OrderCoffret{
        try{
            $this->entityManager->beginTransaction();

            $order->setTva($this->configService->findTva());
            $order->setCoffret($this->coffretRepository->findCoffret());
            $order->setPrixCoffret($order->getCoffret()->getPrix());
            $order->setMontant($order->getPrixCoffret() * $order->getQteCoffret());
            $order->setOrderDate(new DateTime());
            $order->setStatut(OrderCoffret::CREATED);
            
            $this->entityManager->persist($order->getInfo());
            $this->entityManager->persist($order);

            $this->entityManager->flush();
            $this->entityManager->commit();
            
            return $order;
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

    public function payOrder(OrderCoffret $order, $result){
        $order->setChargeId($result);
        $order->setStatut(OrderCoffret::PAIED);
        $this->entityManager->persist($order);
        $this->entityManager->flush();
        try{
            $this->saveInvoice($order);
            $this->sendFacture($order);
        } catch(Exception $ex){}
    }

    public function changeStatus(int $orderId, int $status)
    {
        $order = $this->orderCoffretRepository->find($orderId);
        if(!$order)  {
            throw new Exception("La commande n°".$orderId." n'existe pas");
        }
        $order->setStatut($status);
        $this->entityManager->flush();
    }
    
    public function saveInvoice(OrderCoffret $order){
        $html = $this->mailService->renderTwig('pdf/facture.html.twig', [
            'order' => $order
        ]);
        $binary = $this->wrapper->getPdf($html, ['isRemoteEnabled' => true, 'isHtml5ParserEnabled'=>true, 'defaultFont'=> 'Arial']);
        $invoicePath = $this->fileHandler->saveBinary($binary, "Facture Elephas-Commande n°".$order->getId()." du ".date('Y-m-d-H-i-s').'.pdf', 'factures');
        $order->setInvoicePath($invoicePath);
        $this->entityManager->persist($order);
        $this->entityManager->flush();
    }

    public function sendFacture(OrderCoffret $order){
        $adminEmail = $this->repoUser->find(1)->getMail();

        $body = $this->mailService->renderTwig('emails/commande.html.twig', [
            'order' => $order
        ]);
        $mail = [
            'body' => $body,
            'subject' => 'Commande n°'.$order->getId().' chez Elephas',
            'to' => $order->getInfo()->getEmail(),
        ];
        $attachmentsPath = [$order->getInvoicePath()];
        $embeddedImages = ['logo' => 'assets/utils/images/logo.png'];
        $this->mailService->sendMail($mail, $attachmentsPath, null, $embeddedImages);


        $bodyMailToAdmin = $this->mailService->renderTwig('emails/commande_detail.html.twig', [
            'order' => $order
        ]);
        $MailToAdmin = [
            'body' => $bodyMailToAdmin,
            'subject' => "Commande coffret  n°{$order->getId()}",
            'to' => $adminEmail
        ];

        $this->mailService->sendMail($MailToAdmin, $attachmentsPath, null, $embeddedImages);
    }
}
