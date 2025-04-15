<?php


namespace App\Appaydin\PdUser\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;

class ResettingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', EmailType::class, [
                'attr' => ['placeholder' => ''],
                'label' => 'security.login_username',
            ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
