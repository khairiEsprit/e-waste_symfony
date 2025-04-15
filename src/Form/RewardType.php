<?php

namespace App\Form;

use App\Entity\Reward;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class RewardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Reward Name',
                'attr' => [
                    'placeholder' => 'e.g., Recycling Champion',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter a reward name']),
                ]
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Reward Type',
                'choices' => [
                    'Badge' => Reward::TYPE_BADGE,
                    'Points' => Reward::TYPE_POINTS,
                    'Other' => Reward::TYPE_OTHER,
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please select a reward type']),
                ]
            ])
            ->add('pointsRequired', IntegerType::class, [
                'label' => 'Points Required',
                'attr' => [
                    'placeholder' => 'e.g., 100',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter points required']),
                    new PositiveOrZero(['message' => 'Points must be zero or positive']),
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Describe the reward...',
                    'class' => 'form-control',
                    'rows' => 4
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter a description']),
                ]
            ])
            ->add('imageUrl', UrlType::class, [
                'label' => 'Badge Image URL',
                'required' => false,
                'attr' => [
                    'placeholder' => 'https://example.com/badge.png',
                    'class' => 'form-control'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reward::class,
        ]);
    }
}
