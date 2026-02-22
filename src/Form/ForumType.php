<?php

namespace App\Form;

use App\Entity\Forum;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ForumType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', null, [
                'label' => 'Titre',
                'attr' => [
                    'placeholder' => 'Entrez le titre du forum (minimum 3 caractères)',
                    'class' => 'form-control'
                ],
                'required' => true,
            ])
            ->add('description', null, [
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Entrez la description du forum (minimum 10 caractères)',
                    'rows' => 4,
                    'class' => 'form-control'
                ],
                'required' => true,
            ])
            ->add('date_creation', DateType::class, [
                'label' => 'Date de création',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false,
            ])
            ->add('etat', ChoiceType::class, [
                'label' => 'État',
                'choices' => [
                    'Actif' => 'actif',
                    'Inactif' => 'inactif',
                ],
                'placeholder' => 'Choisir un état',
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => true,
            ])
            ->add('createdBy', null, [
                'label' => 'Créé par',
                'attr' => [
                    'placeholder' => 'Nom du créateur',
                    'class' => 'form-control'
                ],
                'required' => true,
            ])
            ->add('categorie', EntityType::class, [
                'label' => 'Catégorie',
                'class' => 'App\Entity\Categorie',
                'choice_label' => 'titre',
                'placeholder' => 'Choisir une catégorie',
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Forum::class,
        ]);
    }
}
