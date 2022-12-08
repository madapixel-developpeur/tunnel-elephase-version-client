<?php

namespace App\Controller;

use App\Entity\Blog;
use App\Entity\BlogTagBlog;
use App\Form\BlogFilterClientType;
use App\Repository\BlogRepository;
use App\Service\SearchService;
use App\Util\Search\MyCriteriaParam;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/blog')]
class BlogController extends AbstractController
{
    private $entityManager;
    private $blogRepository;
    


    public function __construct(EntityManagerInterface $entityManager, BlogRepository $blogRepository)
    {
        $this->entityManager = $entityManager;
        $this->blogRepository = $blogRepository;
    }

    #[Route('/', name: 'app_blog_index')]
    public function index(Request $request, PaginatorInterface $paginator, SearchService $searchService): Response
    {
        $error = null;
        $page = $request->query->get('page', 1);
        $limit = 6;
        $criteria = [
            ['prop' => 'categorie.id', 'col' => 'id', 'alias' => 'c'],
            ['prop' => 'search', 'col' => "concat(concat(coalesce(b.description, ''), ' '), b.titre)", 'alias' => null, 'op' => 'LIKE']
        ];

        $filter = [];
        
        $form = $this->createForm(BlogFilterClientType::class, $filter, [
            'method' => 'GET'
        ]);
        $form->handleRequest($request);
        $filter = $form->getData();

        $qb = $this->entityManager
            ->createQueryBuilder();
        
        
        $query = $qb
            ->select('b')
            ->from(Blog::class, 'b')
            ->join('b.categorie', 'c')
            ->join('b.auteur', 'a')
        ;  

        $where =  $searchService->getWhere($filter, new MyCriteriaParam($criteria, 'b'));   
        $query->andwhere($where["where"]." and b.statut != 0 ");

        if(isset($filter['tags']) && count($filter['tags']) > 0){
            $tagIdArray = BlogTagBlog::generateTagIdArray($filter['tags']);

            $sqb =  $this->entityManager
            ->createQueryBuilder();
            $query->andWhere(
                $qb->expr()->exists(
                    $sqb->select('bt2')
                    ->from(BlogTagBlog::class, 'bt2')
                    ->join('bt2.tag', 't2')
                    ->join('bt2.blog', 'b2')
                    ->andWhere('b2.id = b.id')
                    ->andWhere('bt2.statut != 0')
                    ->andWhere($sqb->expr()->in('t2.id', $tagIdArray))
                )
            );
        }
        

        $searchService->setAllParameters($query, $where["params"]);
        $searchService->addOrderBy($query, $filter, ['sort' => 'b.datePublication', 'direction' => 'desc']);

        $blogList = $paginator->paginate(
            $query,
            $page,
            $limit
        );

        $recentBlog = $this->blogRepository->getRecentBlog(3);
        
        
        return $this->render('blog/blog_index.html.twig', [
            'error' => $error,
            'blogList' => $blogList,
            'form' => $form->createView(),
            'recentBlog' => $recentBlog
        ]);
    }

    #[Route('/details/{id}', name: 'app_blog_details')]
    public function details(Blog $blog, Request $request): Response
    {
        $error = null;
        $form = $this->createForm(BlogFilterClientType::class, null, [
            'method' => 'GET'
        ]);
        
        $recentBlog = $this->blogRepository->getRecentBlog(3);

        return $this->render('blog/blog_details.html.twig', [
            'error' => $error,
            'blog' => $blog,
            'form' => $form->createView(),
            'recentBlog' => $recentBlog
        ]);
    }
}
