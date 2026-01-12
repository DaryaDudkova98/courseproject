<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

readonly class OAuthUserProvider implements OAuthAwareUserProviderInterface, UserProviderInterface
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function loadUserByOAuthUserResponse(UserResponseInterface $response): UserInterface
    {
        $resourceOwnerName = $response->getResourceOwner()->getName();
        $oauthId = $response->getUsername();
        $email = $response->getEmail();
        $realName = $response->getRealName();

        $user = null;
        if ($resourceOwnerName === 'google') {
            $user = $this->userRepository->findOneBy(['googleId' => $oauthId]);
        }

        if (null === $user && $email) {
            $user = $this->userRepository->findOneBy(['email' => $email]);
        }

        if (null === $user) {
            $user = new User();
            $user->setEmail($email ?? $oauthId . '@' . $resourceOwnerName . '.com');
            $user->setName($realName ?? $resourceOwnerName . ' User');
            $user->setRoles(['ROLE_USER']);
            $user->setIsVerified(true);
            $user->setStatus(User::STATUS_ACTIVE);
            $user->setPassword('');
        }

        if ($resourceOwnerName === 'google' && null === $user->getGoogleId()) {
            $user->setGoogleId($oauthId);
        }

        $this->userRepository->save($user, true);

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->userRepository->findOneBy(['email' => $user->getUserIdentifier()])
            ?? throw new UserNotFoundException();
    }


    public function supportsClass(string $class): bool
    {
        return $class === User::class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->userRepository->findOneBy(['email' => $identifier]);

        if (null === $user) {
            throw new UserNotFoundException(sprintf('User with email "%s" not found.', $identifier));
        }

        return $user;
    }
}
