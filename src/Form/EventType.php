<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('description')
            ->add('dateDebut')
            ->add('dateFin')
            ->add('capacite')
            ->add('image')
            ->add('categorie')
            ->add('prix')
            ->add('lieu')
            ->add('latitude', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'Latitude (auto-rempli par géolocalisation)']
            ])
            ->add('longitude', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'Longitude (auto-rempli par géolocalisation)']
            ])
            ->add('statut', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                'choices' => [
                    'Planifié' => 'planifié',
                    'En cours' => 'en_cours',
                    'Terminé' => 'terminé',
                    'Annulé' => 'annulé',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
