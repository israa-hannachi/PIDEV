<?php

namespace App\Form;

use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'label' => 'Nom de la catégorie',
                'attr' => ['placeholder' => 'Entrez le nom de la catégorie', 'class' => 'w-full px-4 py-2.5 bg-white rounded-xl border border-border placeholder:text-muted-foreground/70 focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10 transition']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 3, 'placeholder' => 'Décrivez la catégorie', 'class' => 'w-full px-4 py-2.5 bg-white rounded-xl border border-border placeholder:text-muted-foreground/70 focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10 transition']
            ])
            ->add('actif', CheckboxType::class, [
                'label' => 'Catégorie active',
                'required' => false,
                'attr' => ['class' => 'h-4 w-4 rounded border-border text-primary focus:ring-primary/20']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categorie::class,
        ]);
    }
}
