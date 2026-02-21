<?php

namespace App\Entity;

use App\Repository\AIModelAdjustmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AIModelAdjustmentRepository::class)]
class AIModelAdjustment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $factorType = null;

    #[ORM\Column(length: 255)]
    private ?string $factorValue = null;

    #[ORM\Column]
    private ?float $adjustmentMultiplier = 1.0;

    #[ORM\Column]
    private ?int $sampleSize = 0;

    #[ORM\Column]
    private ?\DateTimeImmutable $lastUpdated = null;

    #[ORM\Column]
    private bool $isActive = true;

    public function __construct()
    {
        $this->lastUpdated = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFactorType(): ?string
    {
        return $this->factorType;
    }

    public function setFactorType(string $factorType): static
    {
        $this->factorType = $factorType;
        return $this;
    }

    public function getFactorValue(): ?string
    {
        return $this->factorValue;
    }

    public function setFactorValue(string $factorValue): static
    {
        $this->factorValue = $factorValue;
        return $this;
    }

    public function getAdjustmentMultiplier(): ?float
    {
        return $this->adjustmentMultiplier;
    }

    public function setAdjustmentMultiplier(float $adjustmentMultiplier): static
    {
        $this->adjustmentMultiplier = $adjustmentMultiplier;
        return $this;
    }

    public function getSampleSize(): ?int
    {
        return $this->sampleSize;
    }

    public function setSampleSize(int $sampleSize): static
    {
        $this->sampleSize = $sampleSize;
        return $this;
    }

    public function getLastUpdated(): ?\DateTimeImmutable
    {
        return $this->lastUpdated;
    }

    public function setLastUpdated(\DateTimeImmutable $lastUpdated): static
    {
        $this->lastUpdated = $lastUpdated;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }
}
