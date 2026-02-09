<?php

namespace App\Form;

use App\Entity\Meet;
use App\Entity\Participant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EntityType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType as DoctrineEntityType;
use App\Repository\ParticipantRepository;

class MeetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de la réunion',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: Cours de Mathématiques',
                    'class' => 'form-control'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Décrivez le contenu de la réunion...',
                    'rows' => 4,
                    'class' => 'form-control'
                ]
            ])
            ->add('dateDebut', DateTimeType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('dateFin', DateTimeType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('lienMeet', TextType::class, [
                'label' => 'Lien de la réunion (optionnel)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'https://meet.google.com/...',
                    'class' => 'form-control'
                ]
            ])
            ->add('participant', DoctrineEntityType::class, [
                'label' => 'Enseignant responsable',
                'class' => Participant::class,
                'required' => false,
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('p')
                        ->where('p.role = :role')
                        ->setParameter('role', 'enseignant')
                        ->orderBy('p.nom', 'ASC');
                },
                'choice_label' => function ($participant) {
                    return $participant->getNom() . ' ' . $participant->getPrenom();
                },
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('participants', DoctrineEntityType::class, [
                'label' => 'Participants (étudiants) inscrits',
                'class' => Participant::class,
                'multiple' => true,
                'required' => false,
                'query_builder' => function (ParticipantRepository $repository) {
                    return $repository->createQueryBuilder('p')
                        ->where('p.role = :role')
                        ->setParameter('role', 'etudiant')
                        ->orderBy('p.nom', 'ASC');
                },
                'choice_label' => function (Participant $participant) {
                    return $participant->getNom() . ' ' . $participant->getPrenom();
                },
                'attr' => [
                    'class' => 'form-control'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Meet::class,
        ]);
    }
}
