<?php

namespace App\Repository;

use App\Entity\Mobile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Mobile>
 *
 * @method Mobile|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mobile|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mobile[]    findAll()
 * @method Mobile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MobileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mobile::class);
    }

    public function save(Mobile $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Mobile $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllPaginate(int $page, int $limit): array
    {
        $offset = ($page - 1) * $limit;

        return $this->createQueryBuilder('m')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
}
