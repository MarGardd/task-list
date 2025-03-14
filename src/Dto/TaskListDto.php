<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class TaskListDto
{
    #[Assert\NotBlank(message: 'Title is required', groups: ['create', 'update'])]
    public string $title;

    public int $user_id;

    public ?int $id;
}