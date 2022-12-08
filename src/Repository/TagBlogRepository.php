<?php

namespace App\Repository;

use App\Entity\TagBlog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TagBlog>
 *
 * @method TagBlog|null find($id, $lockMode = null, $lockVersion = null)
 * @method TagBlog|null findOneBy(array $criteria, array $orderBy = null)
 * @method TagBlog[]    findAll()
 * @method TagBlog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagBlogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TagBlog::class);
    }

    public function add(TagBlog $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TagBlog $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getValidTags() {
        return $this->createQueryBuilder('t')
            ->andWhere('t.statut != 0')
            ->getQuery()
            ->getResult(); 
    }

//    /**
//     * @return TagBlog[] Returns an array of TagBlog objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TagBlog
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
