<?php

namespace App\Entity;

use App\Repository\RecommendationCacheRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecommendationCacheRepository::class)]
class RecommendationCache
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'recommendationCaches')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'recommendationCaches')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\Column]
    private ?float $matchScore = null;

    #[ORM\Column(type: 'json')]
    private array $factorScores = [];

    #[ORM\Column(type: 'json')]
    private array $explanations = [];

    #[ORM\Column]
    private ?\DateTimeImmutable $computedAt = null;

    #[ORM\Column]
    private bool $isValid = true;

    public function __construct()
    {
        $this->computedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;
        return $this;
    }

    public function getMatchScore(): ?float
    {
        return $this->matchScore;
    }

    public function setMatchScore(float $matchScore): static
    {
        $this->matchScore = $matchScore;
        return $this;
    }

    public function getFactorScores(): array
    {
        return $this->factorScores;
    }

    public function setFactorScores(array $factorScores): static
    {
        $this->factorScores = $factorScores;
        return $this;
    }

    public function getExplanations(): array
    {
        return $this->explanations;
    }

    public function setExplanations(array $explanations): static
    {
        $this->explanations = $explanations;
        return $this;
    }

    public function getComputedAt(): ?\DateTimeImmutable
    {
        return $this->computedAt;
    }

    public function setComputedAt(\DateTimeImmutable $computedAt): static
    {
        $this->computedAt = $computedAt;
        return $this;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function setIsValid(bool $isValid): static
    {
        $this->isValid = $isValid;
        return $this;
    }
}
