<?php

namespace App\Form;

use App\Entity\Demande;
use App\Entity\Traitement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TraitementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Traité' => 'Traité',
                    'Refusé' => 'Refusé',
                    // Add more options as needed
                ],
                'placeholder' => 'Choose status', // Optional: Placeholder text
            ])
            ->add('date_traitement', null, [
                'widget' => 'single_text',
            ])
            ->add('commentaire')
            ->add('demande', EntityType::class, [
                'class' => Demande::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Traitement::class,
        ]);
    }
}
