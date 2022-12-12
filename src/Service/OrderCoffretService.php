<?php
namespace App\Service;

use App\Entity\OrderCoffret;
use App\Repository\CoffretRepository;
use App\Repository\OrderCoffretRepository;
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


    public function __construct(EntityManagerInterface $entityManager, StripeService $stripeService, CoffretRepository $coffretRepository, OrderCoffretRepository $orderCoffretRepository, MailService $mailService, DompdfWrapperInterface $wrapper, FileHandler $fileHandler)
    {
        $this->entityManager = $entityManager;
        $this->stripeService = $stripeService;
        $this->coffretRepository = $coffretRepository;

        $this->orderCoffretRepository = $orderCoffretRepository;
        $this->mailService = $mailService;
        $this->wrapper = $wrapper;
        $this->fileHandler = $fileHandler;
    }

    

    public function saveOrder(OrderCoffret $order, string $stripeToken): ?OrderCoffret{
        try{
            $this->entityManager->beginTransaction();


            $order->setCoffret($this->coffretRepository->findCoffret());
            $order->setPrixCoffret($order->getCoffret()->getPrix());
            $order->setMontant($order->getPrixCoffret() * $order->getQteCoffret());
            $order->setOrderDate(new DateTime());
            $order->setStatut(OrderCoffret::CREATED);
            
            $this->entityManager->persist($order->getInfo());
            $this->entityManager->persist($order);

            
            $chargeId = $this->stripeService
                ->createCharge(
                    $stripeToken, 
                    $order->getMontant(), [
                        'description' => 'Paiement commande'
                    ]);
            $order->setChargeId($chargeId);        

            $this->entityManager->flush();
            $this->entityManager->commit();
            try{
                $this->saveInvoice($order);
                $this->sendFacture($order);
            } catch(Exception $ex) {}
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
        $body = $this->mailService->renderTwig('emails/commande.html.twig', [
            'order' => $order
        ]);
        $mail = [
            'body' => $body,
            'subject' => 'Commande n°'.$order->getId().' chez Elephas',
            'to' => $order->getInfo()->getEmail(),
        ];
        $attachmentsPath = [$order->getInvoicePath()];
        $this->mailService->sendMail($mail, $attachmentsPath);

    }
}
