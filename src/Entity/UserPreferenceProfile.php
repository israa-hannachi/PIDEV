<?php

namespace App\Entity;

use App\Repository\UserPreferenceProfileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserPreferenceProfileRepository::class)]
class UserPreferenceProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'userPreferenceProfile', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'json')]
    private array $preferredCategories = [];

    #[ORM\Column(type: 'json')]
    private array $preferredTopics = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $preferredDifficulty = null;

    #[ORM\Column(type: 'json')]
    private array $preferredDays = [];

    #[ORM\Column]
    private ?float $activityScore = 0.0;

    #[ORM\Column]
    private ?float $profileCompleteness = 0.0;

    #[ORM\Column]
    private ?\DateTimeImmutable $lastComputedAt = null;

    public function __construct()
    {
        $this->lastComputedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getPreferredCategories(): array
    {
        return $this->preferredCategories;
    }

    public function setPreferredCategories(array $preferredCategories): static
    {
        $this->preferredCategories = $preferredCategories;
        return $this;
    }

    public function getPreferredTopics(): array
    {
        return $this->preferredTopics;
    }

    public function setPreferredTopics(array $preferredTopics): static
    {
        $this->preferredTopics = $preferredTopics;
        return $this;
    }

    public function getPreferredDifficulty(): ?string
    {
        return $this->preferredDifficulty;
    }

    public function setPreferredDifficulty(?string $preferredDifficulty): static
    {
        $this->preferredDifficulty = $preferredDifficulty;
        return $this;
    }

    public function getPreferredDays(): array
    {
        return $this->preferredDays;
    }

    public function setPreferredDays(array $preferredDays): static
    {
        $this->preferredDays = $preferredDays;
        return $this;
    }

    public function getActivityScore(): ?float
    {
        return $this->activityScore;
    }

    public function setActivityScore(float $activityScore): static
    {
        $this->activityScore = $activityScore;
        return $this;
    }

    public function getProfileCompleteness(): ?float
    {
        return $this->profileCompleteness;
    }

    public function setProfileCompleteness(float $profileCompleteness): static
    {
        $this->profileCompleteness = $profileCompleteness;
        return $this;
    }

    public function getLastComputedAt(): ?\DateTimeImmutable
    {
        return $this->lastComputedAt;
    }

    public function setLastComputedAt(\DateTimeImmutable $lastComputedAt): static
    {
        $this->lastComputedAt = $lastComputedAt;
        return $this;
    }
}
