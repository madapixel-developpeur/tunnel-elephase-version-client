<?php

namespace App\Controller\Admin;

use App\Entity\OrderCoffret;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProductRepository;
use App\Repository\OrderCoffretRepository as OrderRepository;
use App\Service\FileHandler;
use App\Service\OrderCoffretService as OrderService;
use App\Service\SearchService;
use App\Util\Search\MyCriteriaParam;
use Knp\Component\Pager\PaginatorInterface;
use App\Form\OrderClientFilterType;
use App\Service\OrderCoffretService;

#[Route('/admin/order')]
class AdminOrderController extends AbstractController
{
    private $entityManager;
    private $productRepository;
    private $orderRepository;
    private $fileHandler;
    private $basketService;
    private $orderService;
    private $orderCoffretService;


    public function __construct(EntityManagerInterface $entityManager, 
        ProductRepository $productRepository,
        OrderRepository $orderRepository,
//        BasketService $basketService,
        OrderService $orderService,
        FileHandler $fileHandler,
        OrderCoffretService $orderCoffretService)
    {
        $this->entityManager = $entityManager;
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
//        $this->basketService = $basketService;
        $this->orderService = $orderService;
        //        $this->fileHandler = $fileHandler;
        $this->orderCoffretService = $orderCoffretService;
    }

    #[Route('/', name: 'app_admin_order_index')]
    public function index(Request $request, PaginatorInterface $paginator, SearchService $searchService): Response
    {
        $error = null;
        $page = $request->query->get('page', 1);
        $limit = 10;
        $criteria = [
            ['prop' => 'statut'],
            ['prop' => 'dateMin', 'col' => 'orderDate', 'op' => '>='],
            ['prop' => 'dateMax', 'col' => 'orderDate', 'op' => '<='],
            ['prop' => 'clientName', 'col' => "concat(concat(coalesce(info.nom, ''), ' '), info.prenom)", 'alias' => null, 'op' => 'LIKE']
        ];

        $filter = [];

        $form = $this->createForm(OrderClientFilterType::class, $filter, [
            'method' => 'GET',
            'admin' => true
        ]);
        $form->handleRequest($request);
        $filter = $form->getData();
        $query = $this->entityManager
            ->createQueryBuilder()
            ->select('o')
            ->from(OrderCoffret::class, 'o')
            ->leftJoin('o.info', 'info')

        ;  

        $where =  $searchService->getWhere($filter, new MyCriteriaParam($criteria, 'o'));   
        
        $query->where($where["where"]);
        $searchService->setAllParameters($query, $where["params"]);
        $searchService->addOrderBy($query, $filter, ['sort' => 'o.orderDate', 'direction' => 'desc']);

        $orderList = $paginator->paginate(
            $query,
            $page,
            $limit
        );
        return $this->render('admin/order/admin_order_index.html.twig', [
            'error' => $error,
            'orderList' => $orderList,
            'form' => $form->createView(),
        ]);
    }

    


    #[Route('/validate', name: 'app_admin_order_validate')]
    public function validate(Request $request): Response
    {
        try{
            $order_id = $request->get('order_id', -1);
            $this->orderService->changeStatus($order_id, OrderCoffret::DELIVERED);
            $this->addFlash(
                'success',
                'Commande livrée'
            ); 
        } catch(\Exception $ex){
            $this->addFlash(
               'error',
               $ex->getMessage()
            );
        } 
        return $this->redirectToRoute('app_admin_order_index');
    }

    #[Route('/{id}', name: 'app_admin_order_details')]
    public function details(Request $request, $id): Response
    {
        $error = null;
        $order = $this->orderRepository->find($id);
        if($order == null)  {
            $order = new OrderCoffret();
            $error = "La commande n°".$id." n'existe pas";
        }
        return $this->render('admin/order/admin_order_details.html.twig',[
            'error' => $error,
            'order' => $order
        ]);

    }

}
