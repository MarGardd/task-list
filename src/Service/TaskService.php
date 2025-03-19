<?php

namespace App\Service;

use App\Dto\TaskDto;
use App\Entity\Task;
use App\Entity\TaskList;
use Doctrine\ORM\EntityManagerInterface;

class TaskService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    )
    {}

    public function createTask(TaskDto $taskDto, TaskList $taskList): Task
    {
        $task = new Task();
        $task->setTitle($taskDto->title);
        $task->setDescription($taskDto->description ?? '');
        $task->setTaskList($taskList);
        $this->entityManager->persist($task);
        $this->entityManager->flush();
        $task->setTaskListId($taskList->getId());

        return $task;
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