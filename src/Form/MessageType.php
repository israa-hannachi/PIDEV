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
            ->add('createdBy', CKEditorType::class, [
                'label' => 'Votre nom',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Entrez votre nom...',
                    'class' => 'w-full border rounded p-2'
                ],
                'config' => [
                    'toolbar' => 'full', // barre complète (gras, italique, listes, images, etc.)
                ]
            ])
            ->add('contenu', CKEditorType::class, [ 
                'label' => 'Votre message', 
                'required' => true, 
                'config' => [ 
                    'toolbar' => 'full', // barre complète (gras, italique, listes, images, etc.) 
                ], 
                'attr' => [ 
                    'placeholder' => 'Écrivez votre commentaire...', 
                    'class' => 'w-full border rounded p-2' ] 
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => Message::class,
            'csrf_protection' => true,          // ✅ Protection CSRF activée
            'csrf_field_name' => '_token',      // ✅ Nom du champ caché
            'csrf_token_id'   => 'message_item' // ✅ Identifiant unique pour ce formulaire
        ]);
    }
}
