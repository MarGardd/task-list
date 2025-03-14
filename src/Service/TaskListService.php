<?php

namespace App\Service;

use App\Dto\TaskListDto;
use App\Entity\TaskList;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class TaskListService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    )
    {}

    public function createTaskList(TaskListDto $taskListDto, User $currentUser): TaskList
    {
        $taskList = new TaskList();
        $taskList->setTitle($taskListDto->title);
        $taskList->setUser($currentUser);
        $this->entityManager->persist($taskList);
        $this->entityManager->flush();

        return $taskList;
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