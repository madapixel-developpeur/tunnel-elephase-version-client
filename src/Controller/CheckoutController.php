<?php

namespace App\Controller;


use App\Entity\OrderCoffret;
use App\Form\OrderCoffretFormType;
use App\Repository\CoffretRepository;
use App\Service\OrderCoffretService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route(path: '/checkout')]
class CheckoutController extends AbstractController
{
    private $entityManager;
    private $coffretRepository;
    private $orderCoffretService;

    public function __construct(EntityManagerInterface $entityManager, CoffretRepository $coffretRepository, OrderCoffretService $orderCoffretService)
    {
        $this->entityManager = $entityManager;
        $this->coffretRepository = $coffretRepository;
        $this->orderCoffretService = $orderCoffretService;
    }


    #[Route(path: '/', name: 'app_checkout')]
    public function index(Request $request): Response
    {
        $payment = true;
        $coffret = $this->coffretRepository->findCoffret();
        $order = new OrderCoffret();
        $order->setQteCoffret(1);
        $form = $this->createForm(OrderCoffretFormType::class, $order, ['payment' => $payment]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try{
                $stripeToken =  $form->get('token')->getData();
                $order = $this->orderCoffretService->saveOrder($order, $stripeToken);
                $request->getSession()->remove('order');
                $this->addFlash('success', 'Commande effectuée');
                return $this->redirectToRoute('app_home');
            } catch(Exception $ex){
                $this->addFlash('danger', $ex->getMessage());
            }
        }

        
        return $this->render('home/checkout.html.twig',[
            'form' => $form->createView(),
            'order' => $order,
            'coffret' => $coffret,
            'payment' => $payment
        ]);
        
    }

//    #[Route(path: '/payment', name: 'app_checkout_payment')]
//    public function payment(Request $request): Response
//    {
//        $payment = true;
//        $coffret = $this->coffretRepository->findCoffret();
//        $order = $request->getSession()->get('order');
//        if(!$order) return $this->redirectToRoute('app_checkout');
//        $form = $this->createForm(OrderCoffretFormType::class, $order, ['payment' => $payment]);
//        $form->handleRequest($request);
//        if ($form->isSubmitted() && $form->isValid()) {
//
//            try{
//                $stripeToken =  $form->get('token')->getData();
//                $order = $this->orderCoffretService->saveOrder($order, $stripeToken);
//                $request->getSession()->remove('order');
//                $this->addFlash('success', 'Commande effectuée');
//                return $this->redirectToRoute('app_home');
//            } catch(Exception $ex){
//                $this->addFlash('danger', $ex->getMessage());
//            }
//        }
//
//
//        return $this->render('home/checkout.html.twig',[
//            'form' => $form->createView(),
//            'order' => $order,
//            'coffret' => $coffret,
//            'payment' => $payment
//
//        ]);
//
//    }

    
}
