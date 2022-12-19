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
        $payment = true;
        $coffret = $this->coffretRepository->findCoffret();
        $order = new OrderCoffret();
        $order->setQteCoffret(1);
        $form = $this->createForm(OrderCoffretFormType::class, $order, ['payment' => $payment]);
        $form->handleRequest($request);

        $email = [
            'subject' => 'Commande coffret',
            'body' => 'Texte body'
        ];

        $adminEmail = $this->repoUser->find(1)->getMail();

        if ($form->isSubmitted() && $form->isValid()) {
            try{
                $stripeToken =  $form->get('token')->getData();
                $order = $this->orderCoffretService->saveOrder($order, $stripeToken);
                $request->getSession()->remove('order');
                $this->mailerService->sendMail($email, [$order->getInvoicePath()], $adminEmail);
                $this->addFlash('success', 'Commande effectuée');
                return $this->redirectToRoute('app_home');
            } catch(Exception $ex){
                $this->addFlash('danger', $ex->getMessage());
            }
        }

        $coffretPrice = $coffret->getPrix();
        $stripeIntentSecret = $this->stripeService->intentSecret($coffretPrice);
        $stripe_publishable_key = $_ENV['STRIPE_PUBLIC_KEY'];
        
        return $this->render('home/checkout.html.twig',[
            'form' => $form->createView(),
            'order' => $order,
            'coffret' => $coffret,
            'payment' => $payment,
            'configTva' => $configTva,
            'stripeIntentSecret' => $stripeIntentSecret,
            'stripe_publishable_key' => $stripe_publishable_key,
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
