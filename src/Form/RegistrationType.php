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
            ->add('visitorName', null, ['label' => 'Nom', 'required' => false])
            ->add('visitorEmail', null, ['label' => 'Email', 'required' => false])
            ->add('notes', null, ['required' => false, 'label' => 'Notes'])
            ->add('paymentMethod', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                'choices' => [
                    'Paiement en ligne (Paymee)' => 'paymee',
                    'Paiement en espèces (Sur place)' => 'espece',
                ],
                'label' => 'Méthode de paiement',
                'placeholder' => 'Choisir une méthode',
                'required' => false,
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
