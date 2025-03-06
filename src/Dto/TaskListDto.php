<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class TaskListDto
{
    #[Assert\NotBlank(message: 'Title is required')]
    public string $title;

    #[Assert\NotBlank(message: 'User id is required')]
    public string $user_id;
}