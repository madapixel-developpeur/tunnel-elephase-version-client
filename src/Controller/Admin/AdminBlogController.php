<?php

namespace App\Controller\Admin;

use App\Entity\Blog;
use App\Entity\CategorieBlog;
use App\Form\BlogFilterAdminType;
use App\Form\BlogFormType;
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
use App\Repository\BlogTagBlogRepository;
use App\Service\BlogService;
use DateTime;
use Exception;

#[Route('/admin/blog')]
class AdminBlogController extends AbstractController
{
    private $entityManager;
    private $fileHandler;
    private $blogRepository;
    private $blogService;


    public function __construct(EntityManagerInterface $entityManager, 
        BlogRepository $blogRepository,
        FileHandler $fileHandler,
        BlogService $blogService)
    {
        $this->entityManager = $entityManager;
        $this->blogRepository = $blogRepository;
        $this->fileHandler = $fileHandler;
        $this->blogService = $blogService;

    }

    #[Route('/', name: 'app_admin_blog_index')]
    public function index(Request $request, PaginatorInterface $paginator, SearchService $searchService): Response
    {
        $error = null;
        $page = $request->query->get('page', 1);
        $limit = 5;
        $criteria = [
            ['prop' => 'dateMin', 'col' => 'datePublication', 'op' => '>='],
            ['prop' => 'dateMax', 'col' => 'datePublication', 'op' => '<='],
            ['prop' => 'titre','op' => 'LIKE'],
            ['prop' => 'nomAuteur', 'op' => 'LIKE'],
            ['prop' => 'categorie.id', 'col' => 'id', 'alias' => 'c']
        ];

        $filter = [];

        $form = $this->createForm(BlogFilterAdminType::class, $filter, [
            'method' => 'GET'
        ]);
        $form->handleRequest($request);
        $filter = $form->getData();
        $query = $this->entityManager
            ->createQueryBuilder()
            ->select('b')
            ->from(Blog::class, 'b')
            ->join('b.categorie', 'c')
            ->join('b.auteur', 'a')
        ;  

        $where =  $searchService->getWhere($filter, new MyCriteriaParam($criteria, 'b'));   
        $query->where($where["where"]." and b.statut != 0 ");
        $searchService->setAllParameters($query, $where["params"]);
        $searchService->addOrderBy($query, $filter, ['sort' => 'b.datePublication', 'direction' => 'desc']);

        $blogList = $paginator->paginate(
            $query,
            $page,
            $limit
        );
        return $this->render('admin/blog/admin_blog_index.html.twig', [
            'error' => $error,
            'blogList' => $blogList,
            'form' => $form->createView(),
        ]);
    }

    
    #[Route('/new', name: 'app_admin_blog_new')]
    public function new(Request $request): Response
    {
        $isEdit = false;
        $error = null;
        $user = (object)$this->getUser();
        $blog = new Blog();
        $blog->setNomAuteur($user->getFullName());
        $form = $this->createForm(BlogFormType::class, $blog);
        $form->handleRequest($request);

        
        if ($form->isSubmitted() && $form->isValid()) {

            try{
                $imageFile = $form->get('imageFile')->getData();
                if ($imageFile) {
                    $photo = $this->fileHandler->upload($imageFile, "images\blogs");
                    $blog->setImage($photo);
                }
                $blog->setAuteur($this->getUser());
                $blog->setDatePublication(new DateTime());
                $blog->setStatut(1);
                $this->blogService->saveBlog($blog);
                $this->addFlash('success', 'Article créé');    
                return $this->redirectToRoute('app_admin_blog_details', ['id' => $blog->getId()]);
            } catch(\Exception $ex){
                $error = $ex->getMessage();
            }
        }

        return $this->render('admin/blog/admin_blog_form.html.twig',[
            'form' => $form->createView(),
            'error' => $error,
            'isEdit' => $isEdit,
            'blog' => $blog
        ]);
    }


    #[Route('/edit/{id}', name: 'app_admin_blog_edit')]
    public function edit(Blog $blog, Request $request, BlogTagBlogRepository $blogTagBlogRepository): Response
    {
        $isEdit = true;
        $error = null;

        $blog->setTags($blogTagBlogRepository->getValidTags($blog->getId()));
        $blog->convertTagsToArray();
        $form = $this->createForm(BlogFormType::class, $blog);
        $form->handleRequest($request);

        
        if ($form->isSubmitted() && $form->isValid()) {

            try{
                $imageFile = $form->get('imageFile')->getData();
                if ($imageFile) {
                    $photo = $this->fileHandler->upload($imageFile, "images\blogs");
                    $blog->setImage($photo);
                }

                $this->blogService->saveBlog($blog);
                $this->addFlash('success', 'Article modifié');    
                return $this->redirectToRoute('app_admin_blog_details', ['id' => $blog->getId()]);
            } catch(\Exception $ex){
                $error = $ex->getMessage();
            }
        }

        return $this->render('admin/blog/admin_blog_form.html.twig',[
            'form' => $form->createView(),
            'error' => $error,
            'isEdit' => $isEdit,
            'blog' => $blog
        ]);
    }


    #[Route('/delete/{id}', name: 'app_admin_blog_delete')]
    public function delete(Blog $blog): Response
    {
        try{
            $blog->setStatut(0);
            $this->entityManager->persist($blog);
            $this->entityManager->flush();
            $this->addFlash('success', 'Article supprimé');
            return $this->redirectToRoute('app_admin_blog_index');
        } catch(\Exception $ex){
            throw $ex;
        }
        
    }

    #[Route('/details/{id}', name: 'app_admin_blog_details')]
    public function details(Blog $blog, BlogTagBlogRepository $blogTagBlogRepository): Response
    {
        $error = null;
        $blog->setTags($blogTagBlogRepository->getValidTags($blog->getId()));
        return $this->render('admin/blog/admin_blog_details.html.twig', [
            'error' => $error,
            'blog' => $blog
        ]);
    }
    
}
