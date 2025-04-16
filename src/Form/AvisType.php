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
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Range;

class AvisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Votre nom',
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
                    'placeholder' => 'Votre nom',
                    'id' => 'avis_nom'
                ]
            ])
            ->add('avis', TextareaType::class, [
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
            ->add('note', IntegerType::class, [
                
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez donner une note',
                        'groups' => ['create', 'update']
                    ]),
                    new Range([
                        'min' => 1,
                        'max' => 5,
                        'notInRangeMessage' => 'La note doit être entre {{ min }} et {{ max }}',
                        'groups' => ['create', 'update']
                    ])
                ],
                'attr' => [
                    'class' => 'form-control d-none',
                    'min' => 1,
                    'max' => 5
                ]
            ])
            ->add('image', FileType::class, [
                'label' => 'Image (optionnel)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5m',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, PNG, GIF)',
                        'maxSizeMessage' => 'L\'image ne doit pas dépasser 5 Mo.',
                        'groups' => ['create', 'update']
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*'
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