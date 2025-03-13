<?php

namespace App\Dto;

use App\Entity\TaskList;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;
use App\Constraint\Entity as AppAssert;

class TaskListDto
{
    #[Assert\NotBlank(message: 'Title is required', groups: ['create', 'update'])]
    public string $title;

    #[Assert\NotBlank(message: 'User id is required', groups: ['create'])]
    #[AppAssert\EntityExistence(entityClass: User::class, message: "User with ID '{{ value }}' does not exist.", groups: ['create', 'get'])]
    public int $user_id;

    public ?int $id;

    public function __construct(?TaskList $taskList = null, string $title = '', int $user_id = null)
    {
        $this->id = $taskList?->getId();
        $this->title = $taskList?->getTitle() ?? $title;
        $this->user_id = $taskList?->getUserId() ?? $user_id;
    }
}