<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre est obligatoire")]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le type est obligatoire")]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le niveau est obligatoire")]
    private ?string $niveau = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le score maximum est obligatoire")]
    #[Assert\Positive(message: "Le score maximum doit être positif")]
    private ?int $scoreMax = null;

    #[ORM\Column(nullable: true)]
    private ?int $lastScore = null;

    #[ORM\Column(nullable: true)]
    private ?float $avgScore = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive(message: "La durée doit être un nombre positif.")] 
    #[Assert\LessThanOrEqual(value: 3600, message: "La durée ne peut pas dépasser 1 heure.")]
    private ?int $duration = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Le nombre de tentatives est obligatoire")] 
    #[Assert\Range( min: 0, max: 3, notInRangeMessage: "Le nombre de tentatives doit être entre {{ min }} et {{ max }}" )]
    private ?int $attemptNumber = null;

    #[ORM\Column]
    private ?\DateTime $createdAt = null;

   #[ORM\Column] 
   private ?int $courseId = null ;

    /**
     * @var Collection<int, GameQuestion>
     */
    #[ORM\OneToMany(targetEntity: GameQuestion::class, mappedBy: 'game', orphanRemoval: true)]
    private Collection $questions;

  public function __construct()
{
    $this->questions = new ArrayCollection();

    // ✅ valeurs par défaut
    $this->createdAt = new \DateTime();
    $this->attemptNumber = 0; // ou 1 selon ta logique
    $this->courseId = 0;
}


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getNiveau(): ?string
    {
        return $this->niveau;
    }

    public function setNiveau(string $niveau): static
    {
        $this->niveau = $niveau;

        return $this;
    }

    public function getScoreMax(): ?int
    {
        return $this->scoreMax;
    }

    public function setScoreMax(int $scoreMax): static
    {
        $this->scoreMax = $scoreMax;

        return $this;
    }

    public function getLastScore(): ?int
    {
        return $this->lastScore;
    }

    public function setLastScore(?int $lastScore): static
    {
        $this->lastScore = $lastScore;

        return $this;
    }

    public function getAvgScore(): ?float
    {
        return $this->avgScore;
    }

    public function setAvgScore(?float $avgScore): static
    {
        $this->avgScore = $avgScore;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getAttemptNumber(): ?int
    {
        return $this->attemptNumber;
    }

    public function setAttemptNumber(int $attemptNumber): static
    {
        $this->attemptNumber = $attemptNumber;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCourseId(): ?int
    {
        return $this->courseId;
    }

    public function setCourseId(int $courseId): static
    {
        $this->courseId = $courseId;

        return $this;
    }

    /**
     * @return Collection<int, GameQuestion>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(GameQuestion $question): static
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setGame($this);
        }

        return $this;
    }

    public function removeQuestion(GameQuestion $question): static
    {
        if ($this->questions->removeElement($question)) {
            // set the owning side to null (unless already changed)
            if ($question->getGame() === $this) {
                $question->setGame(null);
            }
        }

        return $this;
    }
}
