<?php

namespace App\Service;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AccessChecker
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker
    ) {}

    public function canCreate($entity): bool
    {
        if (is_object($entity) && method_exists($entity, 'getId') && $entity->getId() === null) {
            return $this->authorizationChecker->isGranted('NEW', get_class($entity));
        }
        return $this->authorizationChecker->isGranted('NEW', $entity);
    }

    public function canView($entity): bool
    {
        return $this->authorizationChecker->isGranted('VIEW', $entity);
    }

    public function canEdit($entity): bool
    {
        return $this->authorizationChecker->isGranted('EDIT', $entity);
    }

    public function canDelete($entity): bool
    {
        return $this->authorizationChecker->isGranted('DELETE', $entity);
    }

    public function canTogglePublic($entity): bool
    {
        return $this->authorizationChecker->isGranted('TOGGLE_PUBLIC', $entity);
    }

    public function canManageWriters($entity): bool
    {
        return $this->authorizationChecker->isGranted('MANAGE_WRITERS', $entity);
    }

    public function checkMultiple($entity, array $attributes): array
    {
        $results = [];
        foreach ($attributes as $attribute) {
            $results[$attribute] = $this->authorizationChecker->isGranted($attribute, $entity);
        }
        return $results;
    }
}
