<?php

// src/Form/CustomRegisterType.php
namespace App\Form;

use Pd\UserBundle\Form\RegisterType as BaseRegisterType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CustomRegisterType extends BaseRegisterType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('selectedRole', ChoiceType::class, [
            'label' => 'security.role',
            'choices' => [
                'security.role_citoyen' => 'ROLE_CITOYEN',
                'security.role_employee' => 'ROLE_EMPLOYEE',
            ],
            'expanded' => false, // Renders as a dropdown
            'multiple' => false, // Single selection
            'mapped' => false,   // Not directly mapped to the User entity
            'required' => true,  // Ensures a role is selected
        ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $user = $form->getData();
            $selectedRole = $form->get('selectedRole')->getData();
            if ($selectedRole) {
                $user->addRole($selectedRole); // Add the selected role to the user's roles array
            }
        });
    }
}