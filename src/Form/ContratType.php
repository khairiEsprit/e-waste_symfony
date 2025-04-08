<?php

namespace App\Form;

use App\Entity\Contrat;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Centre;

class ContratType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('centre', EntityType::class, [
            'class' => Centre::class,
            'choice_label' => 'nom', // Affiche le nom du centre dans le formulaire
        ])
            ->add('id_employe')
            ->add('date_debut', null, [
                'widget' => 'single_text'
            ])
            ->add('date_fin', null, [
                'widget' => 'single_text'
            ])
            ->add('signaturePath')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contrat::class,
        ]);
    }
}
