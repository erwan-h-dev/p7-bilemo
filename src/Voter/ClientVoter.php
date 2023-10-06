<?php

namespace App\Voter;

use App\Entity\Client;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ClientVoter extends Voter
{
    const CAN_SHOW = 'show';
    const CAN_EDIT = 'edit';
    const CAN_DELETE = 'delete';
    
    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!$subject instanceof Client) {
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
            self::CAN_SHOW      => $this->canShow($subject, $currentUser),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    private function canShow(Client $subject, Client $currentUser): bool
    {
        if ($this->isAdmin($currentUser)) {
            return true;
        }

        return ($subject === $currentUser);
    }
    private function isAdmin(Client $currentUser): bool
    {
        return in_array('ROLE_ADMIN', $currentUser->getRoles());
    }
}