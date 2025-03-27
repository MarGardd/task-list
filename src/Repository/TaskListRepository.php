<?php

namespace App\Repository;

use App\Entity\TaskList;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<TaskList>
 */
class TaskListRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly PaginatorInterface $paginator,
    )
    {
        parent::__construct($registry, TaskList::class);
    }

    public function findTaskListsByUserQuery(User $user, ?array $fields = null): Query
    {
        $qb = $this->createQueryBuilder('tl')
            ->leftJoin('tl.tasks', 't')
            ->addSelect('t')
            ->where('tl.user = :user')
            ->setParameter('user', $user);
        if ($fields) {
            $qb->select('tl.' . implode(', tl.', $fields));
        }

        return $qb->orderBy('tl.id', 'ASC')->getQuery();
    }

    public function findPaginatedTaskListsByUser(User $user, int $page = 1, int $pageSize = 10): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->findTaskListsByUserQuery($user),
            $page,
            $pageSize
        );
    }

    public function findAllTaskListsByUser(User $user)
    {
        return $this->findTaskListsByUserQuery($user)->getResult();
    }

    public function save(TaskList $taskList, bool $flush = true): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($taskList);

        if ($flush) {
            $entityManager->flush();
        }
    }

    //    /**
    //     * @return TaskList[] Returns an array of TaskList objects
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

    //    public function findOneBySomeField($value): ?TaskList
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
