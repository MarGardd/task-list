<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly PaginatorInterface $paginator,
    )
    {
        parent::__construct($registry, Task::class);
    }

    public function findTasksByUser(User $user, ?array $fields = null)
    {
        $qb = $this->createQueryBuilder('t')
            ->innerJoin('t.taskList', 'tl')
            ->where('tl.user = :user')
            ->setParameter('user', $user)
            ->orderBy('t.id', 'ASC');
        if ($fields) {
            $qb->select('t.' . implode(', t.', $fields));
        }

        return $qb->getQuery()->getResult();
    }

    public function findPaginatedTasksByUser(User $user, int $page = 1, int $pageSize = 10): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->findTasksByUser(
                $user,
                ['id', 'title', 'description', 'task_list_id']
            ),
            $page,
            $pageSize
        );
    }


    //    /**
    //     * @return Task[] Returns an array of Task objects
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

    //    public function findOneBySomeField($value): ?Task
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
