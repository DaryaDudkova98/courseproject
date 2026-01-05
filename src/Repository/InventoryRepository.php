<?php

namespace App\Repository;

use App\Entity\Inventory;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Inventory>
 */
class InventoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inventory::class);
    }

    /**
     * @return Inventory[]
     */
    public function findAllWithRelations(): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.category', 'c')
            ->leftJoin('i.owner', 'o')
            ->leftJoin('i.writers', 'w')
            ->addSelect('c', 'o', 'w')
            ->orderBy('i.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getQueryBuilderForPagination()
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.category', 'c')
            ->leftJoin('i.owner', 'o')
            ->leftJoin('i.writers', 'w')
            ->addSelect('c', 'o', 'w')
            ->orderBy('i.id', 'DESC');
    }

    public function getQueryForPagination()
    {
        return $this->getQueryBuilderForPagination()->getQuery();
    }

    public function findPublic(): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.category', 'c')
            ->leftJoin('i.owner', 'o')
            ->leftJoin('i.writers', 'w')
            ->addSelect('c', 'o', 'w')
            ->where('i.isPublic = true')
            ->orderBy('i.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByOwner(User $user): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.category', 'c')
            ->leftJoin('i.owner', 'o')
            ->leftJoin('i.writers', 'w')
            ->addSelect('c', 'o', 'w')
            ->where('i.owner = :user')
            ->setParameter('user', $user)
            ->orderBy('i.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByWriter(User $user): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.category', 'c')
            ->leftJoin('i.owner', 'o')
            ->leftJoin('i.writers', 'w')
            ->addSelect('c', 'o', 'w')
            ->where(':user MEMBER OF i.writers')
            ->setParameter('user', $user)
            ->orderBy('i.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findEditableByUser(?User $user = null): array
    {
        $qb = $this->createQueryBuilder('i')
            ->leftJoin('i.category', 'c')
            ->leftJoin('i.owner', 'o')
            ->leftJoin('i.writers', 'w')
            ->addSelect('c', 'o', 'w')
            ->orderBy('i.id', 'DESC');
        
        if ($user) {
            $forbiddenStatuses = ['block', 'delete', 'remove'];
            if (in_array($user->getStatus(), $forbiddenStatuses)) {
                return [];
            }
            
            if (!in_array('ROLE_ADMIN', $user->getRoles())) {
                if ($user->getStatus() === 'active') {

                    $qb->where(
                        $qb->expr()->orX(
                            'i.isPublic = true',
                            'i.owner = :user',
                            'w = :user'
                        )
                    )
                    ->setParameter('user', $user);
                } else {

                    return [];
                }
            }

        } else {

            return [];
        }
        
        return $qb->getQuery()->getResult();
    }

    public function findManageableWritersByUser(?User $user = null): array
    {
        if (!$user) {
            return [];
        }
        
        $forbiddenStatuses = ['block', 'delete', 'remove'];
        if (in_array($user->getStatus(), $forbiddenStatuses)) {
            return [];
        }
        
        $qb = $this->createQueryBuilder('i')
            ->leftJoin('i.category', 'c')
            ->leftJoin('i.owner', 'o')
            ->leftJoin('i.writers', 'w')
            ->addSelect('c', 'o', 'w')
            ->orderBy('i.id', 'DESC');
        
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return $qb->getQuery()->getResult();
        }
        
        if ($user->getStatus() === 'active') {
            $qb->where('i.owner = :user')
               ->setParameter('user', $user);
            return $qb->getQuery()->getResult();
        }
        
        return [];
    }

    /**
     * @return Inventory[] Returns an array of Inventory objects
     */
    public function findByExampleField($value): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function findOneBySomeField($value): ?Inventory
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }
}