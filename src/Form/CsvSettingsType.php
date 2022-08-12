<?php

namespace App\Form;

use App\Services\Import\Readers\CSV\Settings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CsvSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('delimiter', TextType::class, [
                'required' => true,
                'data' => ',',
                'constraints' => [
                    new NotBlank(),
                    new Length(min: 1, max: 1),
                ],
                'attr' => [
                    'minlength' => 1,
                    'maxlength' => 1,
                ],
            ])
            ->add('enclosure', TextType::class, [
                'data' => ' ',
                'constraints' => [
                    new Length(min: 1, max: 1),
                ],
                'attr' => [
                    'minlength' => 1,
                    'maxlength' => 1,
                ],
            ])
            ->add('escape', TextType::class, [
                'data' => ' ',
                'constraints' => [
                    new Length(min: 1, max: 1),
                ],
                'attr' => [
                    'minlength' => 1,
                    'maxlength' => 1,
                ],
            ])
            ->add('haveHeader', CheckboxType::class, [
                'required' => false,
                'data' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Settings::class,
        ]);
    }
}
