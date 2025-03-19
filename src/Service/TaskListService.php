<?php

namespace App\Service;

use App\Dto\TaskListDto;
use App\Entity\TaskList;
use App\Entity\User;
use App\Repository\TaskListRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class TaskListService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TaskListRepository $taskListRepository,
        private readonly CacheInterface $taskListsCache,
    )
    {}

    public function getAllTaskListsByUser(User $user)
    {
        $cacheKey = sprintf('task_lists_user_%d', $user->getId());
        return $this->taskListsCache->get($cacheKey, function (ItemInterface $item) use ($user) {
            $item->expiresAfter(360);
            return $this->taskListRepository->findAllTaskListsByUser($user);
        });
    }

    public function createTaskList(TaskListDto $taskListDto, User $currentUser): TaskList
    {
        $taskList = new TaskList();
        $taskList->setTitle($taskListDto->title);
        $taskList->setUser($currentUser);
        $this->entityManager->persist($taskList);
        $this->entityManager->flush();
        $taskList->setUserId($currentUser->getId());
        $this->invalidateCache($currentUser);

        return $taskList;
    }

    public function updateTaskList(TaskList $taskList, TaskListDto $taskListDto): void
    {
        $taskList->setTitle($taskListDto->title);
        $this->entityManager->flush();
        $this->invalidateCache($taskList->getUser());
    }

    public function deleteTaskList(?TaskList $taskList): void
    {
        $this->entityManager->remove($taskList);
        $this->entityManager->flush();
        $this->invalidateCache($taskList->getUser());
    }

    private function invalidateCache(User $user): void
    {
        $cacheKey = sprintf('task_lists_user_%d', $user->getId());
        $this->taskListsCache->delete($cacheKey);
    }
}