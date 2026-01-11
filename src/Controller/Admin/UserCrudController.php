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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class UserCrudController extends AbstractCrudController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

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
        $actions = $actions
            ->disable(Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_RETURN)
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER);

        $blockAction = Action::new('block', 'Block', 'fa fa-ban')
            ->linkToCrudAction('blockUser')
            ->setCssClass('btn btn-danger')
            ->displayIf(fn(User $user) => $user->getStatus() !== User::STATUS_BLOCKED);

        $unblockAction = Action::new('unblock', 'Unblock', 'fa fa-check')
            ->linkToCrudAction('unblockUser')
            ->setCssClass('btn btn-success')
            ->displayIf(fn(User $user) => $user->getStatus() === User::STATUS_BLOCKED);

        $activateAction = Action::new('activate', 'Active', 'fa fa-check-circle')
            ->linkToCrudAction('activateUser')
            ->setCssClass('btn btn-success')
            ->displayIf(fn(User $user) => $user->getStatus() !== User::STATUS_ACTIVE);

        $makeAdminAction = Action::new('makeAdmin', 'Make Admin', 'fa fa-user-shield')
            ->linkToCrudAction('makeAdmin')
            ->setCssClass('btn btn-warning')
            ->displayIf(fn(User $user) => !in_array('ROLE_ADMIN', $user->getRoles()));

        $removeAdminAction = Action::new('removeAdmin', 'Remove Admin', 'fa fa-user')
            ->linkToCrudAction('removeAdmin')
            ->setCssClass('btn btn-secondary')
            ->displayIf(fn(User $user) => in_array('ROLE_ADMIN', $user->getRoles()));

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $blockAction)
            ->add(Crud::PAGE_INDEX, $unblockAction)
            ->add(Crud::PAGE_INDEX, $activateAction)
            ->add(Crud::PAGE_INDEX, $makeAdminAction)
            ->add(Crud::PAGE_INDEX, $removeAdminAction)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fa fa-plus')->setLabel('Add User');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setIcon('fa fa-trash')->setCssClass('btn btn-danger');
            })
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa fa-eye')->setCssClass('btn btn-info');
            })
            ->reorder(Crud::PAGE_INDEX, [
                Action::DETAIL,
                'block',
                'unblock',
                'activate',
                'makeAdmin',
                'removeAdmin',
                Action::DELETE
            ]);
    }

    public function blockUser(Request $request)
    {
        $userId = $request->query->get('entityId');
        $user = $this->entityManager->getRepository(User::class)->find($userId);

        if ($user) {
            $user->setStatus(User::STATUS_BLOCKED);
            $this->entityManager->flush();
            $this->addFlash('success', sprintf('User "%s" has been blocked', $user->getEmail()));
        } else {
            $this->addFlash('error', 'User not found');
        }

        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => self::class
        ]);
    }

    public function unblockUser(Request $request)
    {
        $userId = $request->query->get('entityId');
        $user = $this->entityManager->getRepository(User::class)->find($userId);

        if ($user) {
            $user->setStatus(User::STATUS_ACTIVE);
            $this->entityManager->flush();
            $this->addFlash('success', sprintf('User "%s" has been unblocked', $user->getEmail()));
        } else {
            $this->addFlash('error', 'User not found');
        }

        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => self::class
        ]);
    }

    public function activateUser(Request $request)
    {
        $userId = $request->query->get('entityId');
        $user = $this->entityManager->getRepository(User::class)->find($userId);

        if ($user) {
            $user->setStatus(User::STATUS_ACTIVE);
            $this->entityManager->flush();
            $this->addFlash('success', sprintf('User "%s" has been activated', $user->getEmail()));
        } else {
            $this->addFlash('error', 'User not found');
        }

        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => self::class
        ]);
    }

    public function makeAdmin(Request $request)
    {
        $userId = $request->query->get('entityId');
        $user = $this->entityManager->getRepository(User::class)->find($userId);

        if ($user) {
            $roles = $user->getRoles();
            if (!in_array('ROLE_ADMIN', $roles)) {
                $roles[] = 'ROLE_ADMIN';
                $user->setRoles($roles);
                $this->entityManager->flush();
                $this->addFlash('success', sprintf('User "%s" has been granted admin privileges', $user->getEmail()));
            } else {
                $this->addFlash('warning', sprintf('User "%s" is already an admin', $user->getEmail()));
            }
        } else {
            $this->addFlash('error', 'User not found');
        }

        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => self::class
        ]);
    }

    public function removeAdmin(Request $request)
    {
        $userId = $request->query->get('entityId');
        $user = $this->entityManager->getRepository(User::class)->find($userId);

        if ($user) {
            $roles = $user->getRoles();
            if (in_array('ROLE_ADMIN', $roles)) {
                $newRoles = array_filter($roles, function($role) {
                    return $role !== 'ROLE_ADMIN';
                });
                
                if (empty($newRoles)) {
                    $newRoles = ['ROLE_USER'];
                }
                
                $user->setRoles(array_values($newRoles));
                $this->entityManager->flush();
                $this->addFlash('success', sprintf('Admin privileges have been removed from user "%s"', $user->getEmail()));
            } else {
                $this->addFlash('warning', sprintf('User "%s" is not an admin', $user->getEmail()));
            }
        } else {
            $this->addFlash('error', 'User not found');
        }

        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => self::class
        ]);
    }
}