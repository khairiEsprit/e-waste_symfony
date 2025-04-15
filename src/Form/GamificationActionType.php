<?php

namespace App\Form;

use App\Entity\GamificationAction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class GamificationActionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Action Name',
                'attr' => [
                    'placeholder' => 'e.g., Recycle Plastic',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter an action name']),
                ]
            ])
            ->add('points', IntegerType::class, [
                'label' => 'Points',
                'attr' => [
                    'placeholder' => 'e.g., 10',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter points value']),
                    new Positive(['message' => 'Points must be a positive number']),
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Describe the action...',
                    'class' => 'form-control',
                    'rows' => 4
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter a description']),
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GamificationAction::class,
        ]);
    }
}
