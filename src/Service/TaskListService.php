<?php

namespace App\Service;

use App\Dto\TaskListDto;
use App\Entity\TaskList;
use App\Entity\User;
use App\Repository\TaskListRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class TaskListService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TaskListRepository $taskListRepository,
        private readonly UserRepository $userRepository
    )
    {}

    public function getTaskListsForUser(User $user): array
    {
        $taskLists = $this->taskListRepository->findTaskListsWithTasksByUser($user);
        return array_map(function ($taskList) {
            return [
                'id' => $taskList->getId(),
                'title' => $taskList->getTitle(),
                'tasks' => array_map(function ($task) {
                    return [
                        'id' => $task->getId(),
                        'title' => $task->getTitle(),
                        'description' => $task->getDescription(),
                    ];
                }, $taskList->getTasks()->toArray()),
            ];
        }, $taskLists);
    }

    public function createTaskList(TaskListDto $taskListDto): int
    {
        $user = $this->userRepository->find($taskListDto->user_id);
        $taskList = new TaskList();
        $taskList->setTitle($taskListDto->title);
        $taskList->setUser($user);
        $this->entityManager->persist($taskList);
        $this->entityManager->flush();

        return $taskList->getId();
    }

    public function updateTaskList(TaskList $taskList, TaskListDto $taskListDto): void
    {
        $taskList->setTitle($taskListDto->title);
        $this->entityManager->flush();
    }

    public function deleteTaskList(?TaskList $taskList): void
    {
        $this->entityManager->remove($taskList);
        $this->entityManager->flush();
    }

}