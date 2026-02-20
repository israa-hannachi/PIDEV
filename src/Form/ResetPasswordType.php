<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Reset Code',
                'attr' => [
                    'placeholder' => 'Enter 6-digit code',
                    'class' => 'form-control',
                    'maxlength' => 6
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Please enter the reset code'
                    ]),
                    new Assert\Length([
                        'min' => 6,
                        'max' => 6,
                        'exactMessage' => 'The reset code must be exactly 6 digits'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^\d{6}$/',
                        'message' => 'The reset code must contain only digits'
                    ])
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'New Password',
                    'attr' => [
                        'placeholder' => 'Enter new password',
                        'class' => 'form-control'
                    ]
                ],
                'second_options' => [
                    'label' => 'Confirm Password',
                    'attr' => [
                        'placeholder' => 'Confirm new password',
                        'class' => 'form-control'
                    ]
                ],
                'invalid_message' => 'The password fields must match',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Please enter a password'
                    ]),
                    new Assert\Length([
                        'min' => 6,
                        'minMessage' => 'Your password must be at least {{ limit }} characters',
                        'max' => 4096
                    ])
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Reset Password',
                'attr' => ['class' => 'btn btn-primary w-100']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
