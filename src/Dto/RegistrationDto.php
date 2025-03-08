<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class RegistrationDto
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    #[Assert\NotBlank]
    public string $password;
}