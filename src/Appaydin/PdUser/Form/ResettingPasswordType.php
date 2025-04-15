<?php



namespace App\Appaydin\PdUser\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;


class ResettingPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'mapped' => false,
                'type' => PasswordType::class,
                'first_options' => [
                    'attr' => ['placeholder' => ''],
                    'label' => 'security.password'
                ],
                'second_options' => [
                    'attr' => ['placeholder' => ''],
                    'label' => 'security.password_confirmation'
                ],
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'max' => 4096,
                    ]),
                ],
                'invalid_message' => 'password_dont_match',
            ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
