<?php

namespace App\Entity;

use App\Repository\GameQuestionRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity(repositoryClass: GameQuestionRepository::class)]
class GameQuestion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

   #[ORM\Column(type: Types::TEXT)]
   #[Assert\NotBlank(message: "La question est obligatoire")]
    private ?string $questionText = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $option1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $option2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $option3 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $option4 = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La réponse correcte est obligatoire")]
    private ?string $correctAnswer = null;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Game $game = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestionText(): ?string
    {
        return $this->questionText;
    }

    public function setQuestionText(string $questionText): static
    {
        $this->questionText = $questionText;

        return $this;
    }

    public function getOption1(): ?string
    {
        return $this->option1;
    }

    public function setOption1(?string $option1): static
    {
        $this->option1 = $option1;

        return $this;
    }

    public function getOption2(): ?string
    {
        return $this->option2;
    }

    public function setOption2(?string $option2): static
    {
        $this->option2 = $option2;

        return $this;
    }

    public function getOption3(): ?string
    {
        return $this->option3;
    }

    public function setOption3(?string $option3): static
    {
        $this->option3 = $option3;

        return $this;
    }

    public function getOption4(): ?string
    {
        return $this->option4;
    }

    public function setOption4(?string $option4): static
    {
        $this->option4 = $option4;

        return $this;
    }

    public function getCorrectAnswer(): ?string
    {
        return $this->correctAnswer;
    }

    public function setCorrectAnswer(string $correctAnswer): static
    {
        $this->correctAnswer = $correctAnswer;

        return $this;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): static
    {
        $this->game = $game;

        return $this;
    }
}
