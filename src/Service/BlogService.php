<?php
namespace App\Service;

use App\Entity\Blog;
use App\Entity\BlogTagBlog;
use Doctrine\ORM\EntityManagerInterface;

class BlogService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function saveBlog(Blog $blog): ?Blog{
        try{
            $this->entityManager->beginTransaction();

            foreach($blog->getTags() as $blogTag){
                $blogTag->setStatut(0);
                $this->entityManager->persist($blogTag);
            }

            if($blog->getTagsArray()){
                foreach($blog->getTagsArray() as $tag){
                    $blogTag = $blog->findBlogTag($tag->getId());
                    if($blogTag){
                        $blogTag->setStatut(1);
                    } else {
                        $blogTag = new BlogTagBlog();
                        $blogTag->setTag($tag);
                        $blogTag->setStatut(1);
                        $blog->addTag($blogTag);
                    }
                    $this->entityManager->persist($blogTag);
                }
            }
            
            $this->entityManager->persist($blog);
            $this->entityManager->flush();
            $this->entityManager->commit();
            return $blog;
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
}