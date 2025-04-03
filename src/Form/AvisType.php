<?php

namespace App\Form;

use App\Entity\Avis;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AvisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom/Prénom',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer votre nom',
                        'groups' => ['create', 'update']
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne doit pas dépasser {{ limit }} caractères',
                        'groups' => ['create', 'update']
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Votre nom complet'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Votre avis',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir votre avis',
                        'groups' => ['create', 'update']
                    ]),
                    new Length([
                        'min' => 10,
                        'max' => 255,
                        'minMessage' => 'L\'avis doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'L\'avis ne doit pas dépasser {{ limit }} caractères',
                        'groups' => ['create', 'update']
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                    'placeholder' => 'Décrivez votre expérience...'
                ]
            ])
            ->add('note', ChoiceType::class, [
                'label' => 'Note',
                'choices' => [
                    '1 étoile' => 1,
                    '2 étoiles' => 2,
                    '3 étoiles' => 3,
                    '4 étoiles' => 4,
                    '5 étoiles' => 5
                ],
                'expanded' => true,
                'multiple' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner une note',
                        'groups' => ['create', 'update']
                    ])
                ],
                'attr' => [
                    'class' => 'star-rating'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Avis::class,
            'attr' => ['class' => 'avis-form'],
            'validation_groups' => ['create', 'update']
        ]);
    }
}