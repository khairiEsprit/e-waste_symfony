<?php

namespace App\Form;

use App\Entity\Reward;
use App\Entity\User;
use App\Entity\UserReward;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class AssignRewardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getFullName() . ' (' . $user->getEmail() . ')';
                },
                'label' => 'User',
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please select a user']),
                ]
            ])
            ->add('reward', EntityType::class, [
                'class' => Reward::class,
                'choice_label' => 'name',
                'label' => 'Reward',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ],
            ])
            ->add('points', IntegerType::class, [
                'label' => 'Bonus Points',
                'attr' => [
                    'placeholder' => 'e.g., 50',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter points value']),
                    new PositiveOrZero(['message' => 'Points must be zero or positive']),
                ]
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Optional notes about this reward assignment',
                    'class' => 'form-control',
                    'rows' => 3
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserReward::class,
        ]);
    }
}
