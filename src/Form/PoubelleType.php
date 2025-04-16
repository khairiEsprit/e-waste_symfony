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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
class PoubelleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('adresse', TextType::class, [
            'label' => 'Adresse',
            'required' => true,
            'attr' => ['class' => 'form-control'],
        ])
        ->add('etat', ChoiceType::class, [
            'label' => 'État',
            'choices' => [
                'Fonctionnel' => 'Fonctionnel',
                'En panne' => 'En panne',
            ],
            'required' => true,
            'attr' => ['class' => 'form-select'],
        ])
        ->add('date_installation', DateType::class, [
            'label' => 'Date d\'installation',
            'widget' => 'single_text',
            'required' => true,
            'attr' => ['class' => 'form-control'],
        ])
        ->add('hauteur_totale', IntegerType::class, [
            'label' => 'Hauteur totale (cm)',
            'required' => true,
            'attr' => ['class' => 'form-control', 'min' => 100, 'max' => 200],
        ])
        ->add('centre', EntityType::class, [
            'class' => Centre::class,
            'choice_label' => 'nom', // Afficher le nom du centre
            'label' => 'Centre',
            'required' => true,
            'attr' => ['class' => 'form-select'],
        ])
        ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $poubelle = $event->getData();
            if ($poubelle->getCapteur()) {
                $poubelle->getCapteur()->setPorteeMaximale($poubelle->getHauteurTotale());
            }
        });
    
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Poubelle::class,
        ]);
    }
}
