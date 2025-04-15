<?php


namespace App\Appaydin\PdUser\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class GroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'security.group_name',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'save',
            ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
