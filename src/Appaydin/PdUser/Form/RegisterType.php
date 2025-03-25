<?php

/**
 * This file is part of the pd-admin pd-user package.
 *
 * @package     pd-user
 * @license     LICENSE
 * @author      Ramazan APAYDIN <apaydin541@gmail.com>
 * @link        https://github.com/appaydin/pd-user
 */

namespace App\Appaydin\PdUser\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
/**
 * User Register Form.
 *
 * @author Ramazan APAYDIN <apaydin541@gmail.com>
 */
class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('email', EmailType::class, [
            'label' => 'security.email',
            'attr' => [
                'placeholder' => ''
            ],
            'constraints' => [
                new Assert\NotBlank(['message' => 'Please enter an email.']),
                new Assert\Email(['message' => 'Please enter a valid email address.']),
            ],
        ])
        ->add('plainPassword', RepeatedType::class, [
            'mapped' => false,
            'type' => PasswordType::class,
            'first_options' => [
                'label' => 'security.password',
                'attr' => [
                    'placeholder' => ''
                ],
            ],
            'second_options' => [
                'label' => 'security.password_confirmation',
                'attr' => [
                    'placeholder' => ''
                ],
            ],
            'invalid_message' => 'password_dont_match',
            'constraints' => [
                new Assert\NotBlank(['message' => 'Please enter a password.']),
                new Assert\Length([
                    'min' => 3,
                    'max' => 4096,
                    'minMessage' => 'Your password must be at least {{ limit }} characters long.',
                    'maxMessage' => 'Your password cannot be longer than {{ limit }} characters.',
                ]),
            ],
        ])
        ->add('selectedRole', ChoiceType::class, [
            'label' => 'security.role',
            'choices' => [
                'security.role_citoyen' => 'ROLE_CITOYEN',
                'security.role_employee' => 'ROLE_EMPLOYEE',
            ],
            'expanded' => false, // Renders as a dropdown
            'multiple' => false, // Single selection
            'mapped' => false,   // Not directly mapped to the User entity
            'required' => true,  // Ensures a role is selected
            'constraints' => [
                new Assert\NotBlank(['message' => 'Please select a role.']),
            ],
        ]);
 
    // Add event listener to set the selected role on the user entity
    $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
        $form = $event->getForm();
        $user = $event->getData();
        $selectedRole = $form->get('selectedRole')->getData();
        if ($selectedRole) {
            $user->addRole($selectedRole); // Assumes your User entity has an addRole method
        }
    });
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
