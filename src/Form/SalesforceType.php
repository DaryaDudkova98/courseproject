<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SalesforceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('first_name', TextType::class, [
                'label' => 'First name *',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'First name is required',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'First name',
                    'class' => 'form-control',
                    'id' => 'first_name'
                ]
            ])
            ->add('last_name', TextType::class, [
                'label' => 'Last name *',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Last name is required',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'Last name',
                    'class' => 'form-control',
                    'id' => 'last_name'
                ]
            ])
            ->add('position', TextType::class, [
                'label' => 'Your Position',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Job title',
                    'class' => 'form-control',
                    'id' => 'position'
                ]
            ])
            ->add('phone', TelType::class, [
                'label' => 'Phone',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Phone number',
                    'class' => 'form-control',
                    'id' => 'phone'
                ]
            ])
            ->add('user_id', HiddenType::class, [
                'required' => true,
                'mapped' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Export to Salesforce',
                'attr' => [
                    'class' => 'btn btn-primary btn-lg',
                ]
            ])
            ->add('cancel', ButtonType::class, [
                'label' => 'Cancel',
                'attr' => [
                    'class' => 'btn btn-outline-secondary',
                    'onclick' => 'window.history.back()',
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'POST',
            'csrf_protection' => true,
        ]);
    }
}