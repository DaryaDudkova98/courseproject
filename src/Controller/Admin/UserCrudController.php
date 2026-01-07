<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('User')
            ->setEntityLabelInPlural('Users')
            ->setPageTitle('index', 'User Management')
            ->setPageTitle('edit', 'Edit User')
            ->setPageTitle('new', 'Create User')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('email')
            ->add('name')
            ->add('status');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            EmailField::new('email')
                ->setLabel('Email')
                ->setRequired(true),
            TextField::new('name')
                ->setLabel('Name')
                ->setRequired(true),
            ChoiceField::new('roles')
                ->setLabel('Roles')
                ->allowMultipleChoices()
                ->setChoices([
                    'User' => 'ROLE_USER',
                    'Admin' => 'ROLE_ADMIN',
                ])
                ->renderExpanded()
                ->renderAsBadges(),
            ChoiceField::new('status')
                ->setLabel('Status')
                ->setChoices([
                    'Unverified' => User::STATUS_UNVERIFIED,
                    'Active' => User::STATUS_ACTIVE,
                    'Blocked' => User::STATUS_BLOCKED,
                ])
                ->renderAsBadges([
                    User::STATUS_ACTIVE => 'success',
                    User::STATUS_BLOCKED => 'danger',
                    User::STATUS_UNVERIFIED => 'warning',
                ]),
            BooleanField::new('isVerified')
                ->setLabel('Verified')
                ->renderAsSwitch(in_array($pageName, [Crud::PAGE_NEW, Crud::PAGE_EDIT])),
            DateTimeField::new('lastSeen')
                ->setLabel('Last Seen')
                ->setFormat('Y-m-d H:i:s')
                ->hideOnIndex()
                ->hideOnForm()
                ->hideOnDetail(),
            TextField::new('theme')
                ->setLabel('Theme')
                ->hideOnIndex(),
            TextField::new('preferred_lang')
                ->setLabel('Preferred Language')
                ->hideOnIndex(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $blockAction = Action::new('block', 'Block', 'fa fa-ban')
            ->linkToCrudAction('blockUser')
            ->displayIf(fn(User $user) => $user->getStatus() !== User::STATUS_BLOCKED);
            
        $unblockAction = Action::new('unblock', 'Unblock', 'fa fa-check')
            ->linkToCrudAction('unblockUser')
            ->displayIf(fn(User $user) => $user->getStatus() === User::STATUS_BLOCKED);
            
        $makeAdminAction = Action::new('makeAdmin', 'Make Admin', 'fa fa-user-shield')
            ->linkToCrudAction('makeAdmin')
            ->displayIf(fn(User $user) => !in_array('ROLE_ADMIN', $user->getRoles()));
            
        $removeAdminAction = Action::new('removeAdmin', 'Remove Admin', 'fa fa-user')
            ->linkToCrudAction('removeAdmin')
            ->displayIf(fn(User $user) => in_array('ROLE_ADMIN', $user->getRoles()));

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $blockAction)
            ->add(Crud::PAGE_INDEX, $unblockAction)
            ->add(Crud::PAGE_INDEX, $makeAdminAction)
            ->add(Crud::PAGE_INDEX, $removeAdminAction)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fa fa-plus')->setLabel('Add User');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa fa-edit');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setIcon('fa fa-trash');
            });
    }

    public function blockUser()
    {
        $this->addFlash('success', 'Block action would be implemented here');
        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => self::class
        ]);
    }
    
    public function unblockUser()
    {
        $this->addFlash('success', 'Unblock action would be implemented here');
        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => self::class
        ]);
    }
    
    public function makeAdmin()
    {
        $this->addFlash('success', 'Make admin action would be implemented here');
        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => self::class
        ]);
    }
    
    public function removeAdmin()
    {
        $this->addFlash('success', 'Remove admin action would be implemented here');
        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => self::class
        ]);
    }
}