<?php

namespace App\Dto;

use App\Entity\Task;
use Symfony\Component\Validator\Constraints as Assert;

class TaskDto
{
    #[Assert\NotBlank(message: 'Title is required', groups: ['create', 'update'])]
    public string $title;

    public string $description;

    public int $task_list_id;

    public ?int $id;

    public function __construct(?Task $task = null)
    {
        if($task) {
            $this->id = $task->getId();
            $this->title = $task->getTitle();
            $this->description = $task->getDescription() ?? '';
            $this->task_list_id = $task->getTaskListId();
        }
    }
}