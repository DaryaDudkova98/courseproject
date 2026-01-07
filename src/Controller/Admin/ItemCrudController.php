<?php

namespace App\Controller\Admin;

use App\Entity\Item;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

class ItemCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Item::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Item')
            ->setEntityLabelInPlural('Items')
            ->setPageTitle('index', 'Items')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('name')->setLabel('Item Name'),
            AssociationField::new('inventory')
                ->setLabel('Belongs to Inventory')
                ->setRequired(false)
                ->setHelp('Select inventory where this item belongs'),
            AssociationField::new('owner')->setLabel('Owner'),
            BooleanField::new('public')->setLabel('Is Public?'),
        ];

        if ($pageName === Crud::PAGE_INDEX) {
    $fields[] = TextField::new('likesCount')
        ->setLabel('Likes')
        ->setTemplatePath('field/likes_count.html.twig')
        ->formatValue(function ($value, $entity) {
            return $entity->getLikes()->count();
        });
}

        if ($pageName === Crud::PAGE_EDIT || $pageName === Crud::PAGE_NEW) {
            $fields[] = AssociationField::new('writers')
                ->setLabel('Writers')
                ->setRequired(false)
                ->setHelp('Users who can write to this item')
                ->setFormTypeOption('multiple', true);
        }

        return $fields;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fa fa-plus')->setLabel('Add New');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa fa-edit');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setIcon('fa fa-trash');
            });
    }
}