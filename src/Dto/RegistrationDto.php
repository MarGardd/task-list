<?php

namespace App\Dto;

use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;
use App\Constraint\Entity as AppAssert;

class RegistrationDto
{
    #[Assert\NotBlank]
    #[Assert\Email]
    #[AppAssert\EntityExistence(
        entityClass: User::class,
        message: 'This email is already in use.',
        field: 'email',
        checkExist: false
    )]
    public string $email;

    #[Assert\NotBlank]
    public string $password;
}