<?php

namespace App\Entity;

use App\Repository\AIPredictionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AIPredictionRepository::class)]
class AIPrediction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'aiPredictions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\Column(length: 255)]
    private ?string $predictionType = null;

    #[ORM\Column]
    private ?float $predictedValue = null;

    #[ORM\Column(nullable: true)]
    private ?float $actualValue = null;

    #[ORM\Column]
    private ?float $confidence = null;

    #[ORM\Column(type: 'json')]
    private array $factors = [];

    #[ORM\Column]
    private ?\DateTimeImmutable $predictionDate = null;

    #[ORM\Column(nullable: true)]
    private ?float $accuracyPercentage = null;

    #[ORM\Column]
    private bool $evaluated = false;

    public function __construct()
    {
        $this->predictionDate = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPredictionType(): ?string
    {
        return $this->predictionType;
    }

    public function setPredictionType(string $predictionType): static
    {
        $this->predictionType = $predictionType;
        return $this;
    }

    public function getPredictedValue(): ?float
    {
        return $this->predictedValue;
    }

    public function setPredictedValue(float $predictedValue): static
    {
        $this->predictedValue = $predictedValue;
        return $this;
    }

    public function getActualValue(): ?float
    {
        return $this->actualValue;
    }

    public function setActualValue(?float $actualValue): static
    {
        $this->actualValue = $actualValue;
        return $this;
    }

    public function getConfidence(): ?float
    {
        return $this->confidence;
    }

    public function setConfidence(float $confidence): static
    {
        $this->confidence = $confidence;
        return $this;
    }

    public function getFactors(): array
    {
        return $this->factors;
    }

    public function setFactors(array $factors): static
    {
        $this->factors = $factors;
        return $this;
    }

    public function getPredictionDate(): ?\DateTimeImmutable
    {
        return $this->predictionDate;
    }

    public function setPredictionDate(\DateTimeImmutable $predictionDate): static
    {
        $this->predictionDate = $predictionDate;
        return $this;
    }

    public function getAccuracyPercentage(): ?float
    {
        return $this->accuracyPercentage;
    }

    public function setAccuracyPercentage(?float $accuracyPercentage): static
    {
        $this->accuracyPercentage = $accuracyPercentage;
        return $this;
    }

    public function isEvaluated(): bool
    {
        return $this->evaluated;
    }

    public function setEvaluated(bool $evaluated): static
    {
        $this->evaluated = $evaluated;
        return $this;
    }
}
