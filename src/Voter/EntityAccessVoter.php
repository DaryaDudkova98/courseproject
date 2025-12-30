<?php

namespace App\Security\Voter;

use App\Entity\AccessibleEntity;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EntityAccessVoter extends Voter
{
    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, ['VIEW', 'EDIT', 'DELETE', 'NEW'])
            && $subject instanceof AccessibleEntity;
    }

    protected function voteOnAttribute(string $attribute, $entity, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var AccessibleEntity $entity */

        switch ($attribute) {
            case 'VIEW':
                return true;

            case 'NEW':
                return $user->getStatus() === 'active';

            case 'EDIT':
                if ($entity->getOwner() === $user) return true;
                if ($entity->isPublic() && $user->getStatus() === 'active') return true;
                if ($entity->getWriters()->contains($user)) return true;
                return false;

            case 'DELETE':
                return in_array('ROLE_ADMIN', $user->getRoles())
                    || $entity->getOwner() === $user;
        }

        return false;
    }
}
