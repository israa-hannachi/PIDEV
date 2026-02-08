<?php

namespace App\Form;

use App\Entity\GameQuestion;
use App\Entity\Game;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class GameQuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('questionText', TextType::class)
            ->add('option1', TextType::class, ['required' => false])
            ->add('option2', TextType::class, ['required' => false])
            ->add('option3', TextType::class, ['required' => false])
            ->add('option4', TextType::class, ['required' => false])
            ->add('correctAnswer', TextType::class)
            ->add('game', EntityType::class, [
    'class' => Game::class,
    'choice_label' => 'titre',
])

            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GameQuestion::class,
        ]);
    }
}
