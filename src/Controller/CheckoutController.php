<?php

namespace App\Controller;


use App\Entity\OrderCoffret;
use App\Form\OrderCoffretFormType;
use App\Repository\CoffretRepository;
use App\Repository\UserRepository;
use App\Service\OrderCoffretService;
use App\Service\ConfigService;
use App\Service\MailService;
use App\Service\StripeService;
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
    private $stripeService;
    private $mailerService;
    private $repoUser;

    public function __construct(EntityManagerInterface $entityManager, CoffretRepository $coffretRepository, OrderCoffretService $orderCoffretService, StripeService $stripeService, MailService $mailerService, UserRepository $repoUser)
    {
        $this->entityManager = $entityManager;
        $this->coffretRepository = $coffretRepository;
        $this->orderCoffretService = $orderCoffretService;
        $this->stripeService = $stripeService;
        $this->mailerService = $mailerService;
        $this->repoUser = $repoUser;
    }


    #[Route(path: '/', name: 'app_checkout')]
    public function index(Request $request, ConfigService $configService): Response
    {
        $configTva = $configService->findTva();
        $coffret = $this->coffretRepository->findCoffret();
        $order = new OrderCoffret();
        $order->setQteCoffret(1);
        $form = $this->createForm(OrderCoffretFormType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try{
                $order = $this->orderCoffretService->saveOrder($order);
                return $this->redirectToRoute('app_checkout_payment', ['id' => $order->getId()]);
            } catch(Exception $ex){
                $this->addFlash('danger', $ex->getMessage());
            }
        }
        
        return $this->render('home/checkout.html.twig',[
            'form' => $form->createView(),
            'order' => $order,
            'coffret' => $coffret,
            'configTva' => $configTva
        ]);
        
    }

    #[Route(path: '/payment/{id}', name: 'app_checkout_payment')]
    public function payment(OrderCoffret $order, Request $request): Response
    {
        $form = $this->createForm(OrderCoffretFormType::class, $order, ['payment' => true, 'attr' => ['readonly' => true]]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try{
                $stripeToken =  $form->get('token')->getData();
                $order = $this->orderCoffretService->payOrder($order, $stripeToken);
                $this->addFlash('success', 'Commande effectuée');
                return $this->redirectToRoute('app_home');
            } catch(Exception $ex){
                $this->addFlash('danger', $ex->getMessage());
            }
        }
        
        $stripeIntentSecret = $this->stripeService->intentSecret($order->getMontant());
        return $this->render('home/payment.html.twig',[
            'form' => $form->createView(),
            'order' => $order,
            'stripeIntentSecret' => $stripeIntentSecret,
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
