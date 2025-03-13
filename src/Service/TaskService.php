<?php

namespace App\Service;

use App\Dto\TaskDto;
use App\Entity\Task;
use App\Entity\TaskList;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class TaskService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
    )
    {}

    public function getTasksByUser(User $user): array
    {
        $userWithTasks = $this->userRepository->findAllTasksWithTaskListsByUser($user);
        $data = [];
        foreach ($userWithTasks->getTaskLists() as $taskList) {
            foreach ($taskList->getTasks() as $task) {
                $data[] = [
                    'id' => $task->getId(),
                    'title' => $task->getTitle(),
                    'description' => $task->getDescription(),
                    'task_list_id' => $taskList->getId(),
                    'task_list_title' => $taskList->getTitle(),
                ];
            }
        }

        return $data;
    }

    public function createTask(TaskDto $taskDto, TaskList $taskList): ?int
    {
        $task = new Task();
        $task->setTitle($taskDto->title);
        $task->setDescription($taskDto->description ?? '');
        $task->setTaskList($taskList);
        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $task->getId();
    }

    public function updateTask(Task $task, TaskDto $taskDto): void
    {
        $task->setTitle($taskDto->title ?? $task->getTitle());
        $task->setDescription($taskDto->description ?? $task->getDescription());
        $this->entityManager->flush();
    }

    public function deleteTask(Task $task): void
    {
        $this->entityManager->remove($task);
        $this->entityManager->flush();
    }
}