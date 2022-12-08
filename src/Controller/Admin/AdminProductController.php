<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ProductFilterType;
use App\Form\ProductFormType;
use App\Service\FileHandler;
use App\Service\SearchService;
use App\Util\PopupUtil;
use App\Util\Search\MyCriteriaParam;
use App\Util\Status;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route(path: '/admin/product')]
class AdminProductController extends AbstractController
{
    private $entityManager;
    private $fileHandler;

    
    public function __construct(EntityManagerInterface $entityManager, 
        FileHandler $fileHandler)
    {
        $this->entityManager = $entityManager;
        $this->fileHandler = $fileHandler;
    }


    #[Route(path: '/', name: 'app_admin_product_index')]
    public function index(Request $request, PaginatorInterface $paginator, SearchService $searchService): Response
    {
        $page = $request->query->get('page', 1);
        $limit = 6;
        $criteria = [
            ['prop' => 'nom', 'op' => 'LIKE'],
            ['prop' => 'prixMin', 'op' => '>=', 'col' => 'prix', 'case_sensitive' => true],
            ['prop' => 'prixMax', 'op' => '<=', 'col' => 'prix', 'case_sensitive' => true],
        ];

        $filter = [];

        $form = $this->createForm(ProductFilterType::class, $filter, [
            'method' => 'GET',
        ]);

        $form->handleRequest($request);
        $filter = $form->getData();
        $query = $this->entityManager
            ->createQueryBuilder()
            ->select('p')
            ->from(Product::class, 'p')
        ;  

        $where =  $searchService->getWhere($filter, new MyCriteriaParam($criteria, 'p'));   
        $query->where($where["where"]." and p.status != 0 ");
        $searchService->setAllParameters($query, $where["params"]);
        $searchService->addOrderBy($query, $filter, ['sort' => 'p.name', 'direction' => 'asc']);

        $productList = $paginator->paginate(
            $query,
            $page,
            $limit
        );

        return $this->render('admin/product/admin_product_index.html.twig', [
            'productList' => $productList,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/new', name: 'app_admin_product_new')]
    public function new(Request $request): Response
    {
        $isEdit = false;
        $product = new Product();

        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try{
                $imageFile = $form->get('imageFile')->getData();
                if ($imageFile) {
                    $photo = $this->fileHandler->upload($imageFile, "images\products");
                    $product->setImage($photo);
                }
                $product->setStatus(Status::VALID);
                $this->entityManager->persist($product);
                $this->entityManager->flush();
                $this->addFlash('success', 'Produit ajouté');
                return $this->redirectToRoute('app_admin_product_index');
            } catch(Exception $ex){
                $this->addFlash('danger', $ex->getMessage());
            }
        }

        
        return $this->render('admin/product/admin_product_form.html.twig',[
            'form' => $form->createView(),
            'isEdit' => $isEdit,
            'product' => $product
        ]);
        
    }

    #[Route('/edit/{id}', name: 'app_admin_product_edit')]
    public function edit(Product $product, Request $request): Response
    {
        $isEdit = true;

        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try{
                $imageFile = $form->get('imageFile')->getData();
                if ($imageFile) {
                    $photo = $this->fileHandler->upload($imageFile, "images\products");
                    $product->setImage($photo);
                }
                $this->entityManager->persist($product);
                $this->entityManager->flush();
                $this->addFlash('success', 'Produit modifié');
                return $this->redirectToRoute('app_admin_product_index');
            } catch(Exception $ex){
                $this->addFlash('danger', $ex->getMessage());
            }
        }

        
        return $this->render('admin/product/admin_product_form.html.twig',[
            'form' => $form->createView(),
            'isEdit' => $isEdit,
            'product' => $product
        ]);
        
    }

    #[Route('/delete/{id}', name: 'app_admin_product_delete')]
    public function delete(Product $product): Response
    {
        try{
            $product->setStatus(Status::INVALID);
            $this->entityManager->persist($product);
            $this->entityManager->flush();
            $this->addFlash('success', 'Produit supprimée');    
        } catch(Exception $ex){
            $this->addFlash('danger', $ex->getMessage());
        }
        return $this->redirectToRoute('app_admin_product_index');
    }

    #[Route('/details/{id}', name: 'app_admin_product_details')]
    public function details(Product $product): Response
    {
        return $this->render('admin/product/admin_product_details.html.twig', [
            'product' => $product
        ]);
    }

    #[Route('/popup', name: 'app_admin_product_popup')]
    public function popup(Request $request, PaginatorInterface $paginator, SearchService $searchService): Response
    {
        $opener = $request->query->get('opener', '');
        $popup = $request->query->get('popup', '');
        $mapPopup = PopupUtil::getMapPopup($opener, $popup);

        $page = $request->query->get('page', 1);
        $limit = 6;
        $criteria = [
            ['prop' => 'name', 'op' => "LIKE"],
            ['prop' => 'prixMin', 'col' => 'cost', 'op' => '>=', 'case_sensitive' => true],
            ['prop' => 'prixMax', 'col' => 'cost', 'op' => '<=', 'case_sensitive' => true]
        ];

        $filter = [];

        $form = $this->createForm(ProductFilterType::class, $filter, [
            'method' => 'GET'
        ]);

        $form->handleRequest($request);
        $filter = $form->getData();

        $query = $this->entityManager
            ->createQueryBuilder()
            ->select('p')
            ->from(Product::class, 'p')
        ;  

        $where =  $searchService->getWhere($filter, new MyCriteriaParam($criteria, 'p'));   
        $query->where($where["where"]);
        $searchService->setAllParameters($query, $where["params"]);
        $searchService->addOrderBy($query, $filter, ['sort' => 'p.name', 'direction' => 'asc']);

        $productList = $paginator->paginate(
            $query,
            $page,
            $limit
        );

        return $this->render('admin/product/admin_product_popup.html.twig', [
            'productList' => $productList,
            'form' => $form->createView(),
            'mapPopup' => $mapPopup,
            'opener' => $opener,
            'popup' => $popup
        ]);

    }
    
}
