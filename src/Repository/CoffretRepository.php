<?php

namespace App\Repository;

use App\Entity\Coffret;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @extends ServiceEntityRepository<Coffret>
 *
 * @method Coffret|null find($id, $lockMode = null, $lockVersion = null)
 * @method Coffret|null findOneBy(array $criteria, array $orderBy = null)
 * @method Coffret[]    findAll()
 * @method Coffret[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoffretRepository extends ServiceEntityRepository
{
    private $params;
    public function __construct(ManagerRegistry $registry, ParameterBagInterface $params)
    {
        parent::__construct($registry, Coffret::class);
        $this->params = $params;
    }

    public function save(Coffret $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Coffret $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findCoffret(): ?Coffret {
        $coffretId = $this->params->get('coffret_id');
        return $this->find($coffretId);
    }

//    /**
//     * @return Coffret[] Returns an array of Coffret objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Coffret
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
