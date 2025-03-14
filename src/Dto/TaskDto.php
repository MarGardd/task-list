<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class TaskDto
{
    #[Assert\NotBlank(message: 'Title is required', groups: ['create', 'update'])]
    public string $title;

    public string $description;

    public int $task_list_id;

    public ?int $id;
}