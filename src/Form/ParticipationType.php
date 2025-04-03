<?php

namespace App\Form;

use App\Entity\Participation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class ParticipationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [
                    new NotBlank(['message' => 'Le prénom est obligatoire']),
                ],
                'attr' => [
                    'placeholder' => 'Votre prénom'
                ]
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est obligatoire']),
                ],
                'attr' => [
                    'placeholder' => 'Votre nom'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new NotBlank(['message' => 'L\'email est obligatoire']),
                ],
                'attr' => [
                    'placeholder' => 'exemple@email.com'
                ]
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'constraints' => [
                    new NotBlank(['message' => 'Le téléphone est obligatoire']),
                    new Length([
                        'min' => 8,
                        'max' => 20,
                        'minMessage' => 'Le téléphone doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le téléphone ne doit pas dépasser {{ limit }} caractères'
                    ])
                ],
                'attr' => [
                    'placeholder' => '0612345678',
                    'maxlength' => 20
                ]
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'constraints' => [
                    new NotBlank(['message' => 'La ville est obligatoire']),
                ],
                'attr' => [
                    'placeholder' => 'Votre ville'
                ]
            ])
            ->add('zipCode', TextType::class, [
                'label' => 'Code postal',
                'constraints' => [
                    new NotBlank(['message' => 'Le code postal est obligatoire']),
                    new Length([
                        'max' => 20,
                        'maxMessage' => 'Le code postal ne doit pas dépasser {{ limit }} caractères'
                    ])
                ],
                'attr' => [
                    'placeholder' => '75001',
                    'maxlength' => 20
                ],
                'empty_data' => ''
            ])
            ->add('country', TextType::class, [
                'label' => 'Pays',
                'constraints' => [
                    new NotBlank(['message' => 'Le pays est obligatoire']),
                ],
                'attr' => [
                    'placeholder' => 'Votre pays'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participation::class,
            'attr' => [
                'novalidate' => 'novalidate'
            ]
        ]);
    }
}