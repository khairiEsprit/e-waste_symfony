<?php

namespace App\Form;

use App\Entity\Capteur;
use App\Entity\Capteurp;
use App\Entity\Centre;
use App\Entity\Poubelle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PoubelleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('adresse')
            ->add('niveau')
            ->add('etat')
            ->add('date_installation', null, [
                'widget' => 'single_text',
            ])
            ->add('hauteur_totale')
            ->add('centre', EntityType::class, [
                'class' => Centre::class,
                'choice_label' => 'id',
            ])
            ->add('capteur', EntityType::class, [
                'class' => Capteur::class,
                'choice_label' => 'id',
            ])
            ->add('capteurp', EntityType::class, [
                'class' => Capteurp::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Poubelle::class,
        ]);
    }
}
