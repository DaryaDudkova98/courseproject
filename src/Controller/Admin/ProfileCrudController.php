<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ProfileCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function createIndexQueryBuilder($entityClass, $sortDirection, $sortField = null, $filters = null): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($entityClass, $sortDirection, $sortField, $filters);

        $currentUser = $this->getUser();

        if ($currentUser && $currentUser instanceof User) {
            $queryBuilder
                ->andWhere('entity.id = :currentUserId')
                ->setParameter('currentUserId', $currentUser->getId());
        } else {
            $queryBuilder->andWhere('1 = 0');
        }

        return $queryBuilder;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Мой профиль и товары')
            ->setEntityLabelInSingular('Профиль')
            ->setEntityLabelInPlural('Профиль')
            ->setDefaultSort(['id' => 'ASC'])
            ->showEntityActionsInlined()
            ->setSearchFields(null)
            ->setPaginatorPageSize(1)
            ->overrideTemplate('crud/index', 'profile/profile.html.twig');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE, Action::BATCH_DELETE, Action::DETAIL, Action::INDEX);
    }

    public function configureFields(string $pageName): iterable
    {
        return [];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters;
    }

    public function index(AdminContext $context)
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($user instanceof User) {
            $ownedItems = $user->getOwnedItems();
            $writableItems = $user->getWritableItems();
        } else {
            $ownedItems = [];
            $writableItems = [];
        }

        return $this->render('profile/profile.html.twig', [
            'user' => $user,
            'ownedItems' => $ownedItems,
            'writableItems' => $writableItems,
            'ea' => $context->getEntity(),
        ]);
    }
}
