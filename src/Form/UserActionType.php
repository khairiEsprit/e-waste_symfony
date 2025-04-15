<?php

namespace App\Form;

use App\Entity\GamificationAction;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserActionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('action', EntityType::class, [
                'class' => GamificationAction::class,
                'choice_label' => function (GamificationAction $action) {
                    return $action->getName() . ' (' . $action->getPoints() . ' points)';
                },
                'label' => 'Select Action',
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please select an action']),
                ]
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Comment (Optional)',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'placeholder' => 'Add any details about your action...',
                    'class' => 'form-control',
                    'rows' => 3
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'user_action',
        ]);
    }
}
