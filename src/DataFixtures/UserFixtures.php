<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
{
    $admin = new User();
    $admin->setEmail('testAdmin@example.com');
    $admin->setRoles(['ROLE_ADMIN']);
    $admin->setName('Test Admin');
    $admin->setStatus('active');
    $admin->setPassword(
        $this->passwordHasher->hashPassword($admin, '1')
    );
    $manager->persist($admin);

    $user = new User();
    $user->setEmail('testUser@example.com');
    $user->setRoles(['ROLE_USER']);
    $user->setName('Test User');
    $user->setStatus('active');
    $user->setPassword(
        $this->passwordHasher->hashPassword($user, '1')
    );
    $manager->persist($user);

    $manager->flush();
}

}
