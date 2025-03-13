<?php

namespace App\Security;

use App\Entity\TaskList;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TaskListVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === 'OWN' && $subject instanceof TaskList;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var TaskList $subject */
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        return $subject->getUser() === $user;
    }
}