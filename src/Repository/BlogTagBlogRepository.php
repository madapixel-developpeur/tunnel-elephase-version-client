<?php

namespace App\Repository;

use App\Entity\BlogTagBlog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BlogTagBlog>
 *
 * @method BlogTagBlog|null find($id, $lockMode = null, $lockVersion = null)
 * @method BlogTagBlog|null findOneBy(array $criteria, array $orderBy = null)
 * @method BlogTagBlog[]    findAll()
 * @method BlogTagBlog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BlogTagBlogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlogTagBlog::class);
    }

    public function add(BlogTagBlog $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BlogTagBlog $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getValidTags(int $blogId) {
        $result = $this->createQueryBuilder('b')
            ->join('b.blog', 'b2')    
            ->andWhere('b.statut != 0')
            ->andWhere('b2.id = :blogId')
            ->setParameter('blogId', $blogId)
            ->getQuery()
            ->getResult(); 
        return new ArrayCollection($result);   
    }

//    /**
//     * @return BlogTagBlog[] Returns an array of BlogTagBlog objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?BlogTagBlog
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
