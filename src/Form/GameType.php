<?php

namespace App\Form;

use App\Entity\Game;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
// Types de champs
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
class GameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
          
  ->add('titre') 
  ->add('type')
   ->add('niveau')
    ->add('scoreMax', IntegerType::class)
     ->add('attemptNumber', IntegerType::class) // 🔹 AJOUT ICI 
     ->add('save', SubmitType::class)
     ->add('duration', IntegerType::class, [
    'label' => 'Durée (en secondes)',
    'required' => false,
    'attr' => [
        'class' => 'form-control',
        'placeholder' => 'Ex: 120 pour 2 minutes'
    ]
])

   ;

            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Game::class,
        ]);
    }
}
