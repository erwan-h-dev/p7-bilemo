<?php

namespace App\Voter;

use App\Entity\User;
use App\Entity\Client;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserVoter extends Voter
{
    const CAN_SHOW = 'show';
    const CAN_CREATE = 'create';
    const CAN_EDIT = 'edit';
    const CAN_DELETE = 'delete';
    
    protected function supports(string $attribute, mixed $subject): bool
    {
        // only vote on `Post` objects
        if (!$subject instanceof User) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $currentUser = $token->getUser();

        if (!$currentUser instanceof Client) {
            return false;
        }

        return match ($attribute) {
            self::CAN_CREATE => $this->canCreate($subject, $currentUser),
            self::CAN_EDIT => $this->canEdit($subject, $currentUser),
            self::CAN_DELETE => $this->canDelete($subject, $currentUser),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    private function canCreate(User $subject, Client $currentUser): bool
    {
        if (in_array('ROLE_ADMIN', $currentUser->getRoles())) {
            return true;
        }

        return false;
    }

    private function canEdit(User $subject, Client $currentUser): bool
    {
        
        if (in_array('ROLE_ADMIN', $currentUser->getRoles())) {
            return true;
        }

        return false;
    }

    private function canDelete(User $subject, Client $currentUser): bool
    {
        if (in_array('ROLE_ADMIN', $currentUser->getRoles())) {
            return true;
        }

        return false;
    }
}