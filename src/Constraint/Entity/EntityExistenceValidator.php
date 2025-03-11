<?php

namespace App\Constraint\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class EntityExistenceValidator extends ConstraintValidator
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {}

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof EntityExistence) {
            throw new UnexpectedTypeException($constraint, EntityExistence::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        $repository = $this->entityManager->getRepository($constraint->entityClass);
        $entity = $repository->findOneBy([$constraint->field => $value]);
        if (
            ($constraint->checkExist && !$entity) ||
            (!$constraint->checkExist && $entity)
        ) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}