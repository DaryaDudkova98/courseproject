<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserStatusChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (User::STATUS_BLOCKED === $user->getStatus()) {
            throw new CustomUserMessageAuthenticationException('user.error.blocked');
        }
        
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (User::STATUS_BLOCKED === $user->getStatus()) {
            throw new CustomUserMessageAccountStatusException('user.error.blocked');
        }
        

        $user->setLastSeen(new \DateTimeImmutable());
        
        if ($user->isUnverified()) {

        }
    }
}
