<?php

namespace App\Form\Admin;

use App\Entity\Cours;
use App\Entity\Module;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CoursAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('description', TextareaType::class, [
                'required' => false,
            ])
            ->add('contenu', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'js-richtext',
                    'rows' => 10,
                ],
            ])
            ->add('fichierContenu', FileType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'text/plain',
                            'application/pdf',
                            'application/x-pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-powerpoint',
                            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                            'application/octet-stream',
                            'application/zip',
                            'image/png',
                            'image/jpeg',
                            'image/jpg',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader un fichier valide (txt, pdf, doc, docx, ppt, pptx, png, jpg)',
                    ]),
                ],
            ])
            ->add('duree', IntegerType::class)
            ->add('ordre', IntegerType::class)
            ->add('actif', CheckboxType::class, [
                'required' => false,
            ])
            ->add('module', EntityType::class, [
                'class' => Module::class,
                'choice_label' => 'titre',
                'placeholder' => 'Choisir un module',
                'query_builder' => function (\App\Repository\ModuleRepository $repository) {
                    return $repository->createQueryBuilder('m')
                        ->orderBy('m.titre', 'ASC');
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cours::class,
        ]);
    }
}
