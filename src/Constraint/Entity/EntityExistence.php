<?php

namespace App\Constraint\Entity;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class EntityExistence extends Constraint
{
    public function __construct(
        public string $entityClass,
        public string $message = "Entity with ID '{{ value }}' does not exist.",
        public string $field = 'id',
        public bool $checkExist = true,
    )
    {
        parent::__construct(['value' => $entityClass]);
    }

    public function getDefaultOption(): string
    {
        return 'entityClass';
    }

    public function getRequiredOptions(): array
    {
        return ['entityClass'];
    }
}