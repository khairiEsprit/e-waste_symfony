<?php

namespace App\Form;

use App\Entity\Plannificationtache;
use App\Entity\tache;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlannificationtacheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('priorite')
            ->add('date_limite', null, [
                'widget' => 'single_text',
            ])
            ->add('id_tache', EntityType::class, [
                'class' => tache::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Plannificationtache::class,
        ]);
    }
}
