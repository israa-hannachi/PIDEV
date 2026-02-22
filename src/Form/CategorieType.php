<?php

namespace App\Form;

use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de la catégorie',
                'attr' => [
                    'placeholder' => 'Ex: Analyse Numérique',
                    'class' => 'form-control'
                ],
                'required' => true
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Décrivez cette catégorie...',
                    'rows' => 3,
                    'class' => 'form-control'
                ],
                'required' => false
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categorie::class,
            // Laisser Symfony gérer le CSRF automatiquement avec ses valeurs par défaut
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'categorie', // ← CORRIGÉ : identifiant unique lié à l'entité
        ]);
    }
}
