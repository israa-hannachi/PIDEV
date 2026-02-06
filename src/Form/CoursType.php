<?php

namespace App\Form;

use App\Entity\Cours;
use App\Entity\Module;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CoursType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', null, [
                'label' => 'Titre du cours',
                'attr' => ['placeholder' => 'Entrez le titre du cours', 'class' => 'w-full px-4 py-2.5 bg-white rounded-xl border border-border placeholder:text-muted-foreground/70 focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10 transition']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 3, 'placeholder' => 'Décrivez le contenu du cours', 'class' => 'w-full px-4 py-2.5 bg-white rounded-xl border border-border placeholder:text-muted-foreground/70 focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10 transition']
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Contenu texte',
                'required' => false,
                'help' => 'Vous pouvez saisir du texte OU uploader un fichier ci-dessous (mais pas les deux)',
                'attr' => ['rows' => 8, 'placeholder' => 'Saisissez le contenu du cours ici...', 'class' => 'js-richtext w-full px-4 py-2.5 bg-white rounded-xl border border-border placeholder:text-muted-foreground/70 focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10 transition']
            ])
            ->add('fichierContenu', FileType::class, [
                'label' => 'Ou uploader un fichier',
                'mapped' => false,
                'required' => false,
                'help' => 'Formats acceptés : txt, pdf, doc, docx, ppt, pptx, png, jpg (max 10Mo)',
                'attr' => ['class' => 'w-full px-4 py-2.5 bg-white rounded-xl border border-border focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10 transition'],
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
                    ])
                ],
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (en minutes)',
                'attr' => ['placeholder' => 'Ex: 45', 'class' => 'w-full px-4 py-2.5 bg-white rounded-xl border border-border placeholder:text-muted-foreground/70 focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10 transition']
            ])
            ->add('ordre', IntegerType::class, [
                'label' => 'Ordre d\'affichage',
                'help' => 'Permet de définir l\'ordre dans lequel les cours apparaissent',
                'attr' => ['placeholder' => 'Ex: 1', 'class' => 'w-full px-4 py-2.5 bg-white rounded-xl border border-border placeholder:text-muted-foreground/70 focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10 transition']
            ])
            ->add('actif', CheckboxType::class, [
                'label' => 'Cours actif',
                'required' => false,
                'attr' => ['class' => 'h-4 w-4 rounded border-border text-primary focus:ring-primary/20']
            ])
            ->add('module', EntityType::class, [
                'class' => Module::class,
                'choice_label' => 'titre',
                'label' => 'Module',
                'placeholder' => 'Choisir un module',
                'disabled' => (bool) $options['lock_module'],
                'attr' => ['class' => 'w-full px-4 py-2.5 bg-white rounded-xl border border-border focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10 transition'],
                'query_builder' => function (\App\Repository\ModuleRepository $repository) {
                    return $repository->createQueryBuilder('m')
                        ->orderBy('m.titre', 'ASC');
                },
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            $form = $event->getForm();
            $data = $event->getData();

            if (!$data instanceof Cours) {
                return;
            }

            $contenuRaw = (string) ($data->getContenu() ?? '');
            $contenuText = html_entity_decode(strip_tags($contenuRaw));
            $contenuText = preg_replace('/\x{00A0}/u', ' ', (string) $contenuText);
            $contenuText = trim((string) $contenuText);
            $hasContenu = $contenuText !== '';

            $uploadedFile = $form->has('fichierContenu') ? $form->get('fichierContenu')->getData() : null;
            $hasUploadedFile = $uploadedFile !== null;

            $existingFileName = trim((string) ($data->getFichierContenu() ?? ''));
            $hasExistingFile = $existingFileName !== '';

            if ($hasContenu && $hasUploadedFile) {
                $message = 'Choisissez un seul mode : contenu texte OU fichier (pas les deux).';
                $form->get('contenu')->addError(new FormError($message));
                $form->get('fichierContenu')->addError(new FormError($message));
            }

            if (!$hasContenu && !$hasUploadedFile && !$hasExistingFile) {
                $message = 'Veuillez saisir un contenu texte ou uploader un fichier.';
                $form->get('contenu')->addError(new FormError($message));
                $form->get('fichierContenu')->addError(new FormError($message));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cours::class,
            'lock_module' => false,
        ]);

        $resolver->setAllowedTypes('lock_module', 'bool');
    }
}
