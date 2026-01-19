<?php

namespace App\Controller\Admin;

use App\Entity\Inventory;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

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
            TextField::new('title')
                ->setLabel('Title')
                ->setRequired(false)
                ->setHelp('Enter inventory item title'),
            AssociationField::new('category')
                ->setLabel('Category (Inventory Name)')
                ->setRequired(true)
                ->setHelp('Select category - it will be used as inventory name'),
            AssociationField::new('tags')
                ->setCrudController(TagCrudController::class)
                ->autocomplete()
                ->setFormTypeOption('by_reference', false),
        ];

        if (in_array($pageName, [Crud::PAGE_NEW, Crud::PAGE_EDIT])) {
            $fields[] = TextEditorField::new('description')
                ->setLabel('Description')
                ->setHelp('Rich text editor. Use the toolbar for formatting.')
                ->setRequired(false)
                ->setNumOfRows(10);
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

        if (in_array($pageName, [Crud::PAGE_NEW, Crud::PAGE_EDIT])) {
            $fields[] = AssociationField::new('writers')
                ->setLabel('Writers')
                ->setHelp('Users who can write to this inventory')
                ->setFormTypeOption('by_reference', false);
        }

        if ($pageName === Crud::PAGE_DETAIL) {
            $fields[] = TextField::new('apiToken', 'API Token for Odoo')
                ->setTemplatePath('inventory/api_token.html.twig')
                ->onlyOnDetail();
            
            $fields[] = TextField::new('apiTokenGeneratedAtFormatted', 'Token Generated At')
        ->onlyOnDetail()
        ->formatValue(function ($value, $entity) {
            $date = $entity->getApiTokenGeneratedAt();
            return $date ? $date->format('d.m.Y H:i') : 'Not generated';
        })
        ->setHelp('Date when API token was generated');
            
            
        }
    
        return $fields;
    }

    public function configureActions(Actions $actions): Actions
    {
        $generateTokenAction = Action::new('generateToken', 'Generate API Token', 'fa fa-key')
            ->linkToCrudAction('generateToken')
            ->setCssClass('btn btn-success')
            ->displayIf(function (Inventory $inventory) {
                return $this->canGenerateToken($inventory, $this->getUser());
            });

        return $actions
            ->add(Crud::PAGE_DETAIL, $generateTokenAction)
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

    public function generateToken(Request $request, AdminUrlGenerator $adminUrlGenerator, ManagerRegistry $doctrine): Response
    {
        $inventoryId = $request->query->get('entityId');
        $entityManager = $doctrine->getManager();
        
        $inventory = $entityManager->getRepository(Inventory::class)->find($inventoryId);
        
        if (!$inventory) {
            throw $this->createNotFoundException('Inventory not found');
        }
        
        if (!$this->canGenerateToken($inventory, $this->getUser())) {
            throw $this->createAccessDeniedException('No permission to generate token');
        }
        
        $inventory->generateApiToken();
        $entityManager->flush();
        
        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($inventoryId)
            ->generateUrl();
        
        $this->addFlash('success', 'API token generated successfully!');
        
        return $this->redirect($url);
    }

    private function canGenerateToken(Inventory $inventory, ?UserInterface $user): bool
    {
        if (!$user) {
            return false;
        }

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        if ($user === $inventory->getOwner()) {
            return true;
        }

        foreach ($inventory->getWriters() as $writer) {
            if ($writer === $user) {
                return true;
            }
        }

        return false;
    }
}