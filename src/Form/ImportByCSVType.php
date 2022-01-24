<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ImportByCSVType extends AbstractType
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
                'data' => '',
                'constraints' => [
                    new Length(min: 0, max: 1),
                ],
                'attr' => [
                    'maxlength' => 1,
                ],
            ])
            ->add('escape', TextType::class, [
                'data' => '',
                'constraints' => [
                    new Length(min: 0, max: 1),
                ],
                'attr' => [
                    'maxlength' => 1,
                ],
            ])
            ->add('file', FileType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('haveHeader', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'data' => true,
            ])
            ->add('testmode', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('import', SubmitType::class, [
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
