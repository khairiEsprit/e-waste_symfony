<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\File;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de l\'événement',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le titre de l\'événement',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le titre est obligatoire.']),
                    new Length([
                        'min' => 5,
                        'max' => 255,
                        'minMessage' => 'Le titre doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le titre ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                    'placeholder' => 'Description détaillée de l\'événement',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'La description est obligatoire.']),
                    new Length([
                        'max' => 1000,
                        'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image de l\'événement',
                'required' => true,
                'mapped' => true,
                'attr' => [
                    'class' => 'form-control d-none',
                    'accept' => 'image/jpeg,image/png,image/gif,image/webp',
                    'data-max-file-size' => '5MB',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner une image.']),
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp',
                        ],
                        'maxSizeMessage' => 'L\'image ne doit pas dépasser 5 Mo.',
                        'mimeTypesMessage' => 'Veuillez sélectionner une image valide (JPEG, PNG, GIF ou WEBP).',
                    ]),
                ],
                'help' => 'Formats acceptés : JPG, PNG, GIF, WEBP. Taille max : 5 Mo.',
            ])
            ->add('remainingPlaces', IntegerType::class, [
                'label' => 'Places disponibles',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'placeholder' => 'Nombre de places restantes',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le nombre de places est obligatoire.']),
                    new PositiveOrZero(['message' => 'Le nombre de places doit être positif ou zéro.']),
                ],
            ])
            ->add('location', TextType::class, [
                'label' => 'Lieu',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Lieu exact de l\'événement',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le lieu est obligatoire.']),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Le lieu ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('date', DateTimeType::class, [
                'label' => 'Date et heureALK',
                'required' => true,
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'class' => 'form-control datetimepicker',
                    'min' => (new \DateTime())->format('Y-m-d\TH:i'),
                ],
                'constraints' => [
                    new NotBlank(['message' => 'La date et l\'heure sont obligatoires.']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
            'attr' => [
                'novalidate' => 'novalidate',
                'class' => 'needs-validation',
            ],
            'error_mapping' => [],
        ]);
    }
}