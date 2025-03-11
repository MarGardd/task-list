<?php

namespace App\Dto;

use App\Entity\Task;
use App\Entity\TaskList;
use Symfony\Component\Validator\Constraints as Assert;
use App\Constraint\Entity as AppAssert;

class TaskDto
{
    #[Assert\NotBlank(message: 'Title is required')]
    public string $title;

    public string $description;

    #[Assert\NotBlank(message: 'Task list id is required')]
    #[AppAssert\EntityExistence(entityClass: TaskList::class, message: "Task list with ID '{{ value }}' does not exist.")]
    public string $task_list_id;

    public ?int $id;

    public function __construct(?Task $task = null, string $title = '', string $description='', string $task_list_id = '')
    {
        $this->id = $task?->getId();
        $this->title = $task?->getTitle() ?? $title;
        $this->description = $task?->getDescription() ?? $description;
        $this->task_list_id = $task?->getTaskListId() ?? $task_list_id;
    }
}