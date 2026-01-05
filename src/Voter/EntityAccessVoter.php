<?php

namespace App\Voter;

use App\Entity\AccessibleEntity;
use App\Entity\Inventory;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EntityAccessVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';
    public const NEW = 'NEW';
    public const TOGGLE_PUBLIC = 'TOGGLE_PUBLIC';
    public const MANAGE_WRITERS = 'MANAGE_WRITERS';

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    protected function supports(string $attribute, $subject): bool
    {
        $attributes = [
            self::VIEW,
            self::EDIT,
            self::DELETE,
            self::NEW,
            self::TOGGLE_PUBLIC,
            self::MANAGE_WRITERS
        ];

        if ($attribute === self::NEW) {
            return in_array($attribute, $attributes)
                && (is_string($subject) || $subject instanceof AccessibleEntity);
        }

        return in_array($attribute, $attributes)
            && $subject instanceof AccessibleEntity;
    }

    protected function voteOnAttribute(string $attribute, $entity, TokenInterface $token): bool
    {
        $user = $token->getUser();

        /** @var AccessibleEntity $entity */

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($entity, $user);

            case self::NEW:
                return $this->canCreate($user);

            case self::EDIT:
                return $this->canEdit($entity, $user);

            case self::DELETE:
                return $this->canDelete($entity, $user);

            case self::TOGGLE_PUBLIC:
                return $this->canTogglePublic($entity, $user);

            case self::MANAGE_WRITERS:
                return $this->canManageWriters($entity, $user);
        }

        return false;
    }

    private function canView(AccessibleEntity $entity, $user): bool
    {
        return true;
    }

    private function canCreate($user): bool
    {

        if (!$user instanceof User) {
            return false;
        }

        if ($user->isBlocked()) {
            return false;
        }

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        return $user->getStatus() === 'active';
    }

    private function canEdit(AccessibleEntity $entity, $user): bool
    {

        if (!$user instanceof User) {
            return false;
        }

        if ($user->isBlocked()) {
            return false;
        }

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        if ($user->getStatus() !== 'active') {
            return false;
        }

        $owner = $this->getOwnerSafe($entity);
        if ($owner && $owner === $user) {
            return true;
        }

        if (method_exists($entity, 'getWriters') && $entity->getWriters()->contains($user)) {
            return true;
        }

        if (method_exists($entity, 'isPublic') && $entity->isPublic()) {
            return true;
        }

        return false;
    }

    private function canDelete(AccessibleEntity $entity, $user): bool
    {

        if (!$user instanceof User) {
            return false;
        }

        if ($user->isBlocked()) {
            return false;
        }

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        if (!$user->isActive()) {
            return false;
        }

        $owner = $this->getOwnerSafe($entity);

        return $owner && $owner === $user;
    }

    private function canTogglePublic(AccessibleEntity $entity, $user): bool
    {
        if (!$user instanceof User) {
            return false;
        }

        if (!method_exists($entity, 'isPublic') || !method_exists($entity, 'setPublic')) {
            return false;
        }

        if ($user->isBlocked()) {
            return false;
        }

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        if (!$user->isActive()) {
            return false;
        }

        $owner = $this->getOwnerSafe($entity);
        if ($owner && $owner === $user) {
            return true;
        }

        if (method_exists($entity, 'getWriters') && $entity->getWriters()->contains($user)) {
            return true;
        }

        return false;
    }

    private function canManageWriters(AccessibleEntity $entity, $user): bool
    {

        if (!$user instanceof User) {
            return false;
        }

        if (!method_exists($entity, 'getWriters')) {
            return false;
        }

        if ($user->isBlocked()) {
            return false;
        }

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        if (!$user->isActive()) {
            return false;
        }

        $owner = $this->getOwnerSafe($entity);

        return $owner && $owner === $user;
    }

    private function canAdminDeleteSelf(User $admin): bool
    {
        $adminRepository = $this->entityManager->getRepository(User::class);

        $totalAdmins = $adminRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('JSON_CONTAINS(u.roles, :role) = 1')
            ->setParameter('role', '"ROLE_ADMIN"')
            ->getQuery()
            ->getSingleScalarResult();

        return $totalAdmins > 1;
    }

    private function getOwnerSafe(AccessibleEntity $entity): ?User
    {
        if (method_exists($entity, 'getOwnerSafe')) {
            return $entity->getOwnerSafe();
        }

        try {
            return $entity->getOwner();
        } catch (\RuntimeException $e) {
            return null;
        }
    }
}
