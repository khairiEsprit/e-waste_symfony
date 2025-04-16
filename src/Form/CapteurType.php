<?php

namespace App\Form;

use App\Entity\Capteur;
use App\Entity\Poubelle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CapteurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('distance_mesuree')
            ->add('date_mesure', null, [
                'widget' => 'single_text',
            ])
            ->add('porteeMaximale')
            ->add('precision_capteur')
            ->add('poubelle', EntityType::class, [
                'class' => Poubelle::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Capteur::class,
        ]);
    }
}
