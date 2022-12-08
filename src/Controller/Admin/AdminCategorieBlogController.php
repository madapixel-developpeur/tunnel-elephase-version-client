<?php

namespace App\Controller\Admin;

use App\Entity\Blog;
use App\Entity\CategorieBlog;
use App\Form\BlogFilterAdminType;
use App\Form\CategorieBlogFilterType;
use App\Form\CategorieBlogFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\FileHandler;
use App\Service\SearchService;
use App\Util\Search\MyCriteriaParam;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\BlogRepository;
use App\Repository\CategorieBlogRepository;
use Exception;

#[Route('/admin/categorie-blog')]
class AdminCategorieBlogController extends AbstractController
{
    private $entityManager;
    private $fileHandler;
    private $categorieBlogRepository;


    public function __construct(EntityManagerInterface $entityManager, 
        CategorieBlogRepository $categorieBlogRepository,
        FileHandler $fileHandler)
    {
        $this->entityManager = $entityManager;
        $this->blogRepository = $categorieBlogRepository;
        $this->fileHandler = $fileHandler;

    }

    #[Route('/', name: 'app_admin_categorie_blog_index')]
    public function index(Request $request, PaginatorInterface $paginator, SearchService $searchService): Response
    {
        $error = null;
        $page = $request->query->get('page', 1);
        $limit = 5;
        $criteria = [
            ['prop' => 'nom','op' => 'LIKE'],
        ];

        $filter = [];

        $form = $this->createForm(CategorieBlogFilterType::class, $filter, [
            'method' => 'GET'
        ]);
        $form->handleRequest($request);
        $filter = $form->getData();
        $query = $this->entityManager
            ->createQueryBuilder()
            ->select('c')
            ->from(CategorieBlog::class, 'c')
        ;  

        $where =  $searchService->getWhere($filter, new MyCriteriaParam($criteria, 'c'));   
        $query->where($where["where"]." and c.statut != 0 ");
        $searchService->setAllParameters($query, $where["params"]);
        $searchService->addOrderBy($query, $filter, ['sort' => 'c.id', 'direction' => 'asc']);

        $categoryList = $paginator->paginate(
            $query,
            $page,
            $limit
        );
        return $this->render('admin/blog/categorie/admin_categorie_blog_index.html.twig', [
            'error' => $error,
            'categoryList' => $categoryList,
            'form' => $form->createView(),
        ]);
    }

    

    #[Route('/new', name: 'app_admin_categorie_blog_new')]
    public function new(Request $request): Response
    {
        $isEdit = false;
        $error = null;
        $categorie = new CategorieBlog();

        $form = $this->createForm(CategorieBlogFormType::class, $categorie);
        $form->handleRequest($request);

    
        if ($form->isSubmitted() && $form->isValid()) {

            try{
                $categorie->setStatut(1);
                $this->entityManager->persist($categorie);
                $this->entityManager->flush();
                $this->addFlash('success', 'Catégorie créée');
                return $this->redirectToRoute('app_admin_categorie_blog_index');
            } catch(\Exception $ex){
                $error = $ex->getMessage();
            }
        }

        return $this->render('admin/blog/categorie/admin_categorie_blog_form.html.twig',[
            'form' => $form->createView(),
            'error' => $error,
            'isEdit' => $isEdit,
            'categorie' => $categorie
        ]);
    }

    #[Route('/edit/{id}', name: 'app_admin_categorie_blog_edit')]
    public function edit(CategorieBlog $categorie, Request $request): Response
    {
        $isEdit = true;
        $error = null;

        $form = $this->createForm(CategorieBlogFormType::class, $categorie);
        $form->handleRequest($request);

    
        if ($form->isSubmitted() && $form->isValid()) {

            try{
                $this->entityManager->persist($categorie);
                $this->entityManager->flush();
                $this->addFlash('success', 'Catégorie modifiée');
                return $this->redirectToRoute('app_admin_categorie_blog_index');
            } catch(\Exception $ex){
                $error = $ex->getMessage();
            }
        }

        return $this->render('admin/blog/categorie/admin_categorie_blog_form.html.twig',[
            'form' => $form->createView(),
            'error' => $error,
            'isEdit' => $isEdit,
            'categorie' => $categorie
        ]);
    }

    #[Route('/delete/{id}', name: 'app_admin_categorie_blog_delete')]
    public function delete(CategorieBlog $categorie): Response
    {
        try{
            $categorie->setStatut(0);
            $this->entityManager->persist($categorie);
            $this->entityManager->flush();
            $this->addFlash('success', 'Catégorie supprimée');
            return $this->redirectToRoute('app_admin_categorie_blog_index');
        } catch(\Exception $ex){
            throw $ex;
        }
        
    }
    
}
