<?php

namespace App\Form;

use App\Entity\Message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('createdBy', TextType::class, [
                'label' => 'Votre nom',
                'required' => true,
                'attr' => [
                    'placeholder' => 'votre nom',
                    'class' => 'form-control'
                ],
                'row_attr' => [
                    'class' => 'blue-bar-field'
                ]
            ])
            ->add('contenu', CKEditorType::class, [
                'label' => 'Votre message',
                'required' => true,
                'config' => [
                    'toolbar' => [
                        ['name' => 'document',    'items' => ['Undo', 'Redo']],
                        ['name' => 'basicstyles', 'items' => ['Bold', 'Italic']],
                        ['name' => 'paragraph',   'items' => ['NumberedList', 'BulletedList']],
                        ['name' => 'links',       'items' => ['Link']],
                        ['name' => 'media',       'items' => ['Image', 'Table']],
                        ['name' => 'tools',       'items' => ['Maximize']]
                    ],
                    'height'         => 200,
                    'uiColor'        => '#ffffff',
                    'removePlugins'  => 'elementspath',
                    'resize_enabled' => true,
                    'allowedContent' => true
                ],
                'attr' => [
                    'placeholder' => 'écrivez votre message',
                    'class'       => 'form-control',
                    'rows'        => 4
                ],
                'row_attr' => [
                    'class' => 'blue-bar-field'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => Message::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'message', // ← CORRIGÉ : identifiant unique lié à l'entité Message
        ]);
    }
}