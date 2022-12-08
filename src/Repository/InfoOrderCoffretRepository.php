<?php

namespace App\Repository;

use App\Entity\InfoOrderCoffret;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InfoOrderCoffret>
 *
 * @method InfoOrderCoffret|null find($id, $lockMode = null, $lockVersion = null)
 * @method InfoOrderCoffret|null findOneBy(array $criteria, array $orderBy = null)
 * @method InfoOrderCoffret[]    findAll()
 * @method InfoOrderCoffret[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InfoOrderCoffretRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InfoOrderCoffret::class);
    }

    public function save(InfoOrderCoffret $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(InfoOrderCoffret $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return InfoOrderCoffret[] Returns an array of InfoOrderCoffret objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?InfoOrderCoffret
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
