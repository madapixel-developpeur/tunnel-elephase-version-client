<?php
namespace App\Service;

use App\Entity\OrderCoffret;
use App\Repository\CoffretRepository;
use App\Repository\OrderCoffretRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class OrderCoffretService
{
    private $entityManager;
    private $stripeService;
    private $coffretRepository;
    private OrderCoffretRepository $orderCoffretRepository;


    public function __construct(EntityManagerInterface $entityManager, StripeService $stripeService, CoffretRepository $coffretRepository, OrderCoffretRepository $orderCoffretRepository)
    {
        $this->entityManager = $entityManager;
        $this->stripeService = $stripeService;
        $this->coffretRepository = $coffretRepository;

        $this->orderCoffretRepository = $orderCoffretRepository;
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
    
}
