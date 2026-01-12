<?php

namespace App\Controller\Admin;

use App\Entity\CustomField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;

class CustomFieldCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CustomField::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Custom Field')
            ->setEntityLabelInPlural('Custom Fields')
            ->setPageTitle('index', 'Custom Fields')
            ->setPageTitle('new', 'Create Custom Field')
            ->setPageTitle('edit', 'Edit Custom Field')
            ->setDefaultSort(['sortOrder' => 'ASC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('title')
            ->setLabel('Field Name')
            ->setHelp('Display name in forms');

        yield TextareaField::new('description')
            ->setLabel('Description')
            ->setHelp('Tooltip text shown in forms')
            ->hideOnIndex();

        yield ChoiceField::new('type')
            ->setLabel('Field Type')
            ->setChoices([
                'Text (single line)' => 'text_single',
                'Text (multi line)' => 'text_multi',
                'Number' => 'number',
                'Document/Image' => 'document',
                'True/False' => 'boolean',
            ])
            ->setHelp('Maximum 3 fields of each type');

        yield BooleanField::new('showInTable')
            ->setLabel('Show in Table')
            ->setHelp('Display in inventory table');

        yield IntegerField::new('sortOrder')
            ->setLabel('Order')
            ->hideOnIndex();

        yield BooleanField::new('isActive')
            ->setLabel('Active')
            ->hideOnIndex();
    }
}
