<?php

namespace App\Form;

use App\Entity\Historique;
use App\Entity\Poubelle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HistoriqueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_evenement', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'form-control', 'max' => (new \DateTime())->format('Y-m-d\TH:i')],
                'label' => 'Date de l\'événement',
                'required' => true,
            ])
            ->add('type_evenement', ChoiceType::class, [
                'choices' => [
                    'Collecte' => 'Collecte',
                    'Traitement' => 'Traitement',
                    'Recyclage' => 'Recyclage',
                    'Maintenance' => 'Maintenance'
                ],
                'attr' => ['class' => 'form-select'],
                'label' => 'Type d\'événement',
                'placeholder' => 'Sélectionnez un type',
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Description détaillée de l\'événement'
                ],
                'label' => 'Description',
                'required' => false,
            ])
            ->add('quantite_dechets', NumberType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'max' => 1000,
                    'step' => 0.1
                ],
                'label' => 'Quantité de déchets (kg)',
                'required' => true,
            ])
            ->add('poubelle', EntityType::class, [
                'class' => Poubelle::class,
                'choice_label' => function(Poubelle $poubelle) {
                    return sprintf('Poubelle #%d - %s', $poubelle->getId(), $poubelle->getAdresse());
                },
                'attr' => ['class' => 'form-select'],
                'label' => 'Poubelle concernée',
                'placeholder' => 'Sélectionnez une poubelle',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Historique::class,
        ]);
    }
}
