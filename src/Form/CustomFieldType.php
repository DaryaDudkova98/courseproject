<?php
// src/Form/CustomFieldType.php

namespace App\Form;

use App\Entity\CustomField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class CustomFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'attr' => [
                    'placeholder' => 'Field title',
                    'maxlength' => 100,
                ],
                'help' => 'Maximum 100 characters',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Description shown as tooltip in forms',
                    'rows' => 3,
                    'maxlength' => 500,
                ],
                'help' => 'Optional. Maximum 500 characters',
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Field Type',
                'choices' => CustomField::getAvailableTypes(),
                'placeholder' => 'Select field type...',
                'attr' => [
                    'class' => 'field-type-selector',
                ],
                'help' => 'Maximum 3 fields of each type',
            ])
            ->add('showInTable', CheckboxType::class, [
                'label' => 'Show in inventory table',
                'required' => false,
                'label_attr' => ['class' => 'form-check-label'],
                'help' => 'Display this field in the inventory table view',
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Sort Order',
                'required' => false,
                'attr' => [
                    'min' => 0,
                    'placeholder' => '0',
                ],
                'help' => 'Lower numbers appear first',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CustomField::class,
        ]);
    }
}