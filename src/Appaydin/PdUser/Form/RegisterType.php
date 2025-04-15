<?php


namespace App\Appaydin\PdUser\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use DateTime;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'security.first_name',
                'attr' => ['placeholder' => ''],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter your first name.']),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'security.email',
                'attr' => ['placeholder' => ''],
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
                    'attr' => ['placeholder' => ''],
                ],
                'second_options' => [
                    'label' => 'security.password_confirmation',
                    'attr' => ['placeholder' => ''],
                ],
                'invalid_message' => 'password_dont_match',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter a password.']),
                    new Assert\Length([
                        'min' => 8,
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
            ])

            ->add('birthdate', DateType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'MM/dd/yyyy',
                'attr' => [
                    'class' => 'js-datepicker',
                    'placeholder' => 'MM/DD/YYYY'
                ],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter your birthdate.']),
                    new Assert\LessThanOrEqual([
                        'value' => '-18 years',
                        'message' => 'You must be at least 18 years old.'
                    ])
                ]
            ])
            ->add('profileImageFile', FileType::class, [
                'label' => 'security.profile_image',
                'mapped' => false, // Ensures the file is not automatically mapped to the entity
                'required' => false,
                'constraints' => [
                    new Assert\NotNull([
                        'message' => 'Please upload an image file.',
                    ]),
                    new Assert\File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                        'mimeTypesMessage' => 'Please upload a valid image (JPEG, PNG, GIF).',
                    ]),
                ],
            ]);

        // Add event listener to set the selected role on the user entity
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $user = $event->getData();
            $selectedRole = $form->get('selectedRole')->getData();
            if ($selectedRole) {
                $user->addRole($selectedRole);
            }
        });
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
