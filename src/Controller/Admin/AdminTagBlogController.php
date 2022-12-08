<?php

namespace App\Controller\Admin;

use App\Entity\TagBlog;
use App\Form\TagBlogFilterType;
use App\Form\TagBlogFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\FileHandler;
use App\Service\SearchService;
use App\Util\Search\MyCriteriaParam;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\TagBlogRepository;
use Exception;

#[Route('/admin/tag-blog')]
class AdminTagBlogController extends AbstractController
{
    private $entityManager;
    private $fileHandler;
    private $tagBlogRepository;


    public function __construct(EntityManagerInterface $entityManager, 
        TagBlogRepository $tagBlogRepository,
        FileHandler $fileHandler)
    {
        $this->entityManager = $entityManager;
        $this->tagBlogRepository = $tagBlogRepository;
        $this->fileHandler = $fileHandler;

    }

    #[Route('/', name: 'app_admin_tag_blog_index')]
    public function index(Request $request, PaginatorInterface $paginator, SearchService $searchService): Response
    {
        $error = null;
        $page = $request->query->get('page', 1);
        $limit = 5;
        $criteria = [
            ['prop' => 'nom','op' => 'LIKE'],
        ];

        $filter = [];

        $form = $this->createForm(TagBlogFilterType::class, $filter, [
            'method' => 'GET'
        ]);
        $form->handleRequest($request);
        $filter = $form->getData();
        $query = $this->entityManager
            ->createQueryBuilder()
            ->select('t')
            ->from(TagBlog::class, 't')
        ;  

        $where =  $searchService->getWhere($filter, new MyCriteriaParam($criteria, 't'));   
        $query->where($where["where"]." and t.statut != 0 ");
        $searchService->setAllParameters($query, $where["params"]);
        $searchService->addOrderBy($query, $filter, ['sort' => 't.id', 'direction' => 'asc']);

        $tagList = $paginator->paginate(
            $query,
            $page,
            $limit
        );
        return $this->render('admin/blog/tag/admin_tag_blog_index.html.twig', [
            'error' => $error,
            'tagList' => $tagList,
            'form' => $form->createView(),
        ]);
    }

    

    #[Route('/new', name: 'app_admin_tag_blog_new')]
    public function new(Request $request): Response
    {
        $isEdit = false;
        $error = null;
        $tag = new TagBlog();

        $form = $this->createForm(TagBlogFormType::class, $tag);
        $form->handleRequest($request);

    
        if ($form->isSubmitted() && $form->isValid()) {

            try{
                $tag->setStatut(1);
                $this->entityManager->persist($tag);
                $this->entityManager->flush();
                $this->addFlash('success', 'Tag créé');
                return $this->redirectToRoute('app_admin_tag_blog_index');
            } catch(\Exception $ex){
                $error = $ex->getMessage();
            }
        }

        return $this->render('admin/blog/tag/admin_tag_blog_form.html.twig',[
            'form' => $form->createView(),
            'error' => $error,
            'isEdit' => $isEdit,
            'tag' => $tag
        ]);
    }

    #[Route('/edit/{id}', name: 'app_admin_tag_blog_edit')]
    public function edit(TagBlog $tag, Request $request): Response
    {
        $isEdit = true;
        $error = null;

        $form = $this->createForm(TagBlogFormType::class, $tag);
        $form->handleRequest($request);

    
        if ($form->isSubmitted() && $form->isValid()) {

            try{
                $this->entityManager->persist($tag);
                $this->entityManager->flush();
                $this->addFlash('success', 'Tag modifié');
                return $this->redirectToRoute('app_admin_tag_blog_index');
            } catch(\Exception $ex){
                $error = $ex->getMessage();
            }
        }

        return $this->render('admin/blog/tag/admin_tag_blog_form.html.twig',[
            'form' => $form->createView(),
            'error' => $error,
            'isEdit' => $isEdit,
            'tag' => $tag
        ]);
    }

    #[Route('/delete/{id}', name: 'app_admin_tag_blog_delete')]
    public function delete(TagBlog $tag): Response
    {
        try{
            $tag->setStatut(0);
            $this->entityManager->persist($tag);
            $this->entityManager->flush();
            $this->addFlash('success', 'Tag supprimé');
            return $this->redirectToRoute('app_admin_tag_blog_index');
        } catch(\Exception $ex){
            throw $ex;
        }
        
    }
    
}
