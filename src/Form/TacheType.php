<?php

namespace App\Form;

use App\Entity\Tache;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TacheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id_centre')
            ->add('id_employe')
            ->add('altitude')
            ->add('longitude')
            ->add('message')
            ->add('etat', ChoiceType::class, [
                'choices' => [
                    'Terminé' => 'Terminé',
                    'En cours' => 'En cours',
                    'En attente' => 'En attente',
                ],
                'required' => true,
                'placeholder' => 'Choisir un état',
                'attr' => [
                    'class' => 'form-select',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tache::class,
        ]);
    }
}