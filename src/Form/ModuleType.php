<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Module;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModuleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', null, [
                'label' => 'Titre du module',
                'attr' => ['placeholder' => 'Entrez le titre du module', 'class' => 'w-full px-4 py-2.5 bg-white rounded-xl border border-border placeholder:text-muted-foreground/70 focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10 transition']
            ])
            ->add('description', null, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 3, 'placeholder' => 'Décrivez le contenu du module', 'class' => 'w-full px-4 py-2.5 bg-white rounded-xl border border-border placeholder:text-muted-foreground/70 focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10 transition']
            ])
            ->add('duree', null, [
                'label' => 'Durée (en heures)',
                'attr' => ['placeholder' => 'Ex: 20', 'class' => 'w-full px-4 py-2.5 bg-white rounded-xl border border-border placeholder:text-muted-foreground/70 focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10 transition']
            ])
            ->add('niveau', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                'label' => 'Niveau',
                'choices' => [
                    'Débutant' => 'Débutant',
                    'Intermédiaire' => 'Intermédiaire',
                    'Avancé' => 'Avancé',
                ],
                'attr' => ['class' => 'w-full px-4 py-2.5 bg-white rounded-xl border border-border focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10 transition']
            ])
            ->add('actif', \Symfony\Component\Form\Extension\Core\Type\CheckboxType::class, [
                'label' => 'Module actif',
                'required' => false,
                'attr' => ['class' => 'h-4 w-4 rounded border-border text-primary focus:ring-primary/20']
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'label' => 'Catégorie',
                'placeholder' => 'Choisir une catégorie',
                'disabled' => (bool) $options['lock_categorie'],
                'attr' => ['class' => 'w-full px-4 py-2.5 bg-white rounded-xl border border-border focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10 transition']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Module::class,
            'lock_categorie' => false,
        ]);

        $resolver->setAllowedTypes('lock_categorie', 'bool');
    }
}
