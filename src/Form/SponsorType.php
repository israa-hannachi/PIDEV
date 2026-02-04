<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Sponsor;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SponsorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('description')
            ->add('logo')
            ->add('siteWeb')
            ->add('type')
            ->add('montant')
            ->add('dateDebut')
            ->add('dateFin')
            ->add('statut')
            ->add('contactPersonne')
            ->add('contactEmail')
            ->add('contactTelephone')
            ->add('dateCreation')
            ->add('event', EntityType::class, [
                'class' => Event::class,
                'choice_label' => 'titre',
                'label' => 'Événement'

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sponsor::class,
        ]);
    }
}
