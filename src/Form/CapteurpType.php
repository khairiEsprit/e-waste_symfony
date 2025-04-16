<?php

namespace App\Form;

use App\Entity\Capteurp;
use App\Entity\Poubelle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CapteurpType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantite')
            ->add('date_m', null, [
                'widget' => 'single_text',
            ])
            ->add('poubelle', EntityType::class, [
                'class' => Poubelle::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Capteurp::class,
        ]);
    }
}
