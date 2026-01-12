<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class GoogleAuthenticator extends OAuth2Authenticator
{
    private $clientRegistry;
    private $entityManager;
    private $router;

    public function __construct(
        ClientRegistry $clientRegistry,
        EntityManagerInterface $entityManager,
        RouterInterface $router
    ) {
        $this->clientRegistry = $clientRegistry;
        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                /** @var \League\OAuth2\Client\Provider\GoogleUser $googleUser */
                $googleUser = $client->fetchUserFromToken($accessToken);
                $email = $googleUser->getEmail();
                $googleId = $googleUser->getId();
                $name = $googleUser->getName();

                $user = $this->entityManager->getRepository(User::class)
                    ->findOneBy(['email' => $email]);

                if (!$user) {
                    $user = $this->entityManager->getRepository(User::class)
                        ->findOneBy(['googleId' => $googleId]);
                }

                if (!$user) {
                    $user = new User();
                    $user->setEmail($email);
                    $user->setName($name);
                    $user->setGoogleId($googleId);
                    $user->setStatus(User::STATUS_ACTIVE);
                    $user->setIsVerified(true);
                    $user->setRoles(['ROLE_USER']);

                    $this->entityManager->persist($user);
                    $this->entityManager->flush();
                } else {
                    if (!$user->getGoogleId()) {
                        $user->setGoogleId($googleId);
                    }
                    if ($user->isUnverified()) {
                        $user->setStatus(User::STATUS_ACTIVE);
                        $user->setIsVerified(true);
                    }
                    $this->entityManager->flush();
                }

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->router->generate('homepage'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new Response('Authentication failed: ' . $exception->getMessage(), Response::HTTP_FORBIDDEN);
    }
}
