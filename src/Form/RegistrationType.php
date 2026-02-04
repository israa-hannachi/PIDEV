<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Registration;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('visitorName', null, ['label' => 'Nom'])
            ->add('visitorEmail', null, ['label' => 'Email'])
            ->add('modePaiement', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                'choices' => [
                    'Gratuit' => 'gratuit',
                    'Carte Bancaire' => 'carte',
                    'Espèces' => 'espèces',
                    'Virement' => 'virement',
                    'PayPal' => 'paypal',
                ],
                'label' => 'Mode de paiement'
            ])
            ->add('montantPaye', null, ['label' => 'Montant à payer'])
            ->add('notes', null, ['required' => false])
            ->add('evenement', EntityType::class, [
                'class' => Event::class,
                'choice_label' => 'titre',
                'label' => 'Événement'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Registration::class,
        ]);
    }
}
