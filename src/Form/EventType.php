<?php

namespace App\Form;

use App\Entity\Event;
use App\Service\ICalService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Event Title',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter event title']
            ])
            ->add('description', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 5, 'placeholder' => 'Detailed event description']
            ])
            ->add('dateDebut', \Symfony\Component\Form\Extension\Core\Type\DateTimeType::class, [
                'label' => 'Start Date & Time',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('dateFin', \Symfony\Component\Form\Extension\Core\Type\DateTimeType::class, [
                'label' => 'End Date & Time',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('capacite', IntegerType::class, [
                'label' => 'Capacity',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('image', FileType::class, [
                'label' => 'Event Image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Please upload a valid image (JPG, PNG, WEBP)',
                    ])
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('categorie', ChoiceType::class, [
                'label' => 'Category',
                'required' => false,
                'choices' => [
                    'Technologie' => 'Technologie',
                    'Formation' => 'Formation',
                    'Networking' => 'Networking',
                    'Sport' => 'Sport',
                    'Musique' => 'Musique',
                    'Art' => 'Art',
                    'Gaming' => 'Gaming',
                    'Autre' => 'Autre',
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('prix', \Symfony\Component\Form\Extension\Core\Type\MoneyType::class, [
                'label' => 'Price',
                'currency' => 'TND',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('lieu', TextType::class, [
                'label' => 'Location',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Event location address']
            ])
            ->add('latitude', TextType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'Latitude (auto-fill via geolocation)', 'class' => 'form-control']
            ])
            ->add('longitude', TextType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'Longitude (auto-fill via geolocation)', 'class' => 'form-control']
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Planned' => 'planifié',
                    'In Progress' => 'en_cours',
                    'Completed' => 'terminé',
                    'Cancelled' => 'annulé',
                ],
                'attr' => ['class' => 'form-control'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
