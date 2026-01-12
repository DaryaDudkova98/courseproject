<?php

namespace App\Controller\Admin;

use App\Entity\Item;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;

class ItemCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

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
            ->setPageTitle('new', 'New Item')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(20);
    }


    public function index(AdminContext $context): KeyValueStore|Response
    {

        return parent::index($context);
    }

    public function new(AdminContext $context): Response
{
    $item = new Item();
    $user = $this->getUser();
    
    if ($user) {
        $item->setOwner($user);
    }
    
    $form = $this->createFormBuilder($item)
        ->add('name', TextType::class, [
            'label' => 'Item Name',
            'required' => true,
            'attr' => ['class' => 'form-control']
        ])
        ->add('description', TextareaType::class, [
            'label' => 'Description',
            'required' => false,
            'attr' => ['class' => 'form-control', 'rows' => 4]
        ])
        ->add('inventory', EntityType::class, [
            'class' => 'App\Entity\Inventory',
            'label' => 'Belongs to Inventory',
            'required' => false,
            'attr' => ['class' => 'form-select']
        ])
        ->add('public', CheckboxType::class, [
            'label' => 'Public',
            'required' => false,
            'attr' => ['class' => 'form-check-input']
        ])
        ->add('writers', EntityType::class, [
            'class' => 'App\Entity\User',
            'label' => 'Writers',
            'required' => false,
            'multiple' => true,
            'choice_label' => 'email',
            'attr' => ['class' => 'form-select']
        ])
        ->getForm();
    
    $form->handleRequest($context->getRequest());
    
    if ($form->isSubmitted() && $form->isValid()) {
        $this->entityManager->persist($item);
        $this->entityManager->flush();

        $this->addFlash('success', 'Item created successfully!');
        
        return $this->redirectToRoute('admin', [
            'crudControllerFqcn' => self::class,
            'crudAction' => 'index',
        ]);
    }
    

    return $this->render('item/new_with_tabs.html.twig', [
        'item' => $item,
        'form' => $form->createView(),
        'page_title' => 'New Item',

    ]);
}

    public function edit(AdminContext $context)
    {
        return parent::edit($context);
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
            BooleanField::new('public')->setLabel('Public'),
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
                return $action
                    ->setIcon('fa fa-plus')
                    ->setLabel('Add New')
                    ->linkToCrudAction('new');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa fa-edit');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setIcon('fa fa-trash');
            });
    }
}