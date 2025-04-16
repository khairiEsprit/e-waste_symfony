<?php
namespace App\Form;

use App\Entity\Centre;
use App\Entity\Demande;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class DemandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('adresse')
            ->add('email_utilisateur', type:EmailType::class)
            ->add('message')
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Reclamation' => 'Reclamation',
                    'Demande' => 'Demande',
                    // Add more options as needed
                ],
                'placeholder' => 'Choose a type', // Optional: Placeholder text
            ])
            ->add('utilisateur', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
                'attr' => ['style' => 'display: none;'], // Hide the field
                'label' => false, // Hide the label as well
                'mapped' => false, // Make it not part of the form directly
                'data' => $options['current_user'], // Assign the user data directly
            ])
            ->add('centre', EntityType::class, [
                'class' => Centre::class,
                'choice_label' => 'id',
                'attr' => ['style' => 'display: none;'], // Hide the field
                'label' => false, // Hide the label as well
                'mapped' => false, // Make it not part of the form directly
                'data' => $options['current_centre'], // Assign the centre data directly
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Demande::class,
            'current_user' => null, // You need to set this in the controller
            'current_centre' => null, // You need to set this in the controller
        ]);
    }
}