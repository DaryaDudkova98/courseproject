<?php

namespace App\Controller\Admin;

use App\Entity\Inventory;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use FOS\CKEditorBundle\Form\Type\CKEditorType;

class InventoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Inventory::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Inventory Item')
            ->setEntityLabelInPlural('Inventory')
            ->setPageTitle('index', 'Inventory')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            IdField::new('id')->onlyOnIndex(),
            AssociationField::new('category')
                ->setLabel('Category (Inventory Name)')
                ->setRequired(true)
                ->setHelp('Select category - it will be used as inventory name'),
        ];

        if (in_array($pageName, [Crud::PAGE_NEW, Crud::PAGE_EDIT])) {
            $fields[] = TextareaField::new('description')
                ->setLabel('Description')
                ->setHelp('Rich text editor. Use the toolbar for formatting.')
                ->setRequired(false)
                ->setFormType(CKEditorType::class)
                ->setFormTypeOption('config', [
                    'toolbar' => 'full',
                    'height' => 300,
                    'uiColor' => '#f7f7f7',
                ]);
        } else {
            $fields[] = TextareaField::new('description')
                ->setLabel('Description')
                ->hideOnIndex()
                ->onlyOnDetail()
                ->renderAsHtml();
        }

        $fields[] = AssociationField::new('owner')
            ->setRequired(false)
            ->setHelp('Select the owner of this inventory');
        
        $fields[] = BooleanField::new('public')
            ->renderAsSwitch(true);

        if ($pageName === Crud::PAGE_DETAIL) {
            $fields[] = CollectionField::new('items')
                ->setLabel('Items in this Inventory')
                ->setTemplatePath('admin/inventory/items.html.twig')
                ->onlyOnDetail();
        }
    
        return $fields;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
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