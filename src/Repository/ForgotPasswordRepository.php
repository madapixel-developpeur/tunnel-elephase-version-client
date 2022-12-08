<?php

namespace App\Repository;

use App\Entity\ForgotPassword;
use App\Util\Status;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ForgotPassword>
 *
 * @method ForgotPassword|null find($id, $lockMode = null, $lockVersion = null)
 * @method ForgotPassword|null findOneBy(array $criteria, array $orderBy = null)
 * @method ForgotPassword[]    findAll()
 * @method ForgotPassword[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForgotPasswordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ForgotPassword::class);
    }

    public function add(ForgotPassword $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ForgotPassword $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getValidForgotPwd($user, $verifCode): ?ForgotPassword
    {
        return $this->createQueryBuilder('f')
           ->andWhere('f.status = :status')
           ->andWhere('f.user = :user')
           ->andWhere('lower(f.verifCode) = lower(:verifCode)')
           ->andWhere('f.dateExpiration > :dateNow')
           ->setParameter('status', Status::VALID)
           ->setParameter('user', $user)
           ->setParameter('verifCode', sha1($verifCode))
           ->setParameter('dateNow', new DateTime())
           ->getQuery()
           ->getOneOrNullResult()
       ;
    }

//    /**
//     * @return ForgotPassword[] Returns an array of ForgotPassword objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ForgotPassword
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
