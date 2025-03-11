<?php

namespace App\Dto;

use App\Entity\TaskList;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;
use App\Constraint\Entity as AppAssert;

class TaskListDto
{
    #[Assert\NotBlank(message: 'Title is required')]
    public string $title;

    #[Assert\NotBlank(message: 'User id is required')]
    #[AppAssert\EntityExistence(entityClass: User::class, message: "User with ID '{{ value }}' does not exist.")]
    public string $user_id;

    public ?int $id;

    public function __construct(?TaskList $taskList = null, string $title = '', string $user_id = '')
    {
        $this->id = $taskList?->getId();
        $this->title = $taskList?->getTitle() ?? $title;
        $this->user_id = $taskList?->getUserId() ?? $user_id;
    }
}