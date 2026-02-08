<?php

namespace App\Entity;

use App\Repository\CoursRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CoursRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Cours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: "Le titre du cours est obligatoire")]
    #[Assert\Length(
        min: 3,
        max: 150,
        minMessage: "Le titre doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        max: 1000,
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contenu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fichierContenu = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La durée du cours est obligatoire")]
    #[Assert\NotBlank(message: "La durée du cours est obligatoire")]
    #[Assert\Positive(message: "La durée doit être un nombre positif")]
    private ?int $duree = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "L'ordre d'affichage est obligatoire")]
    #[Assert\NotBlank(message: "L'ordre d'affichage est obligatoire")]
    #[Assert\PositiveOrZero(message: "L'ordre doit être un nombre positif ou zéro")]
    private ?int $ordre = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateModification = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Le statut (actif/inactif) est obligatoire")]
    private ?bool $actif = null;

    #[ORM\ManyToOne(inversedBy: 'cours')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le module est obligatoire")]
    private ?Module $module = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->actif = true;
        $this->ordre = 0;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->dateModification = new \DateTime();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(?string $contenu): static
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): static
    {
        $this->ordre = $ordre;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateModification(): ?\DateTimeInterface
    {
        return $this->dateModification;
    }

    public function setDateModification(?\DateTimeInterface $dateModification): static
    {
        $this->dateModification = $dateModification;

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;

        return $this;
    }

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function setModule(?Module $module): static
    {
        $this->module = $module;

        return $this;
    }

    public function getFichierContenu(): ?string
    {
        return $this->fichierContenu;
    }

    public function setFichierContenu(?string $fichierContenu): static
    {
        $this->fichierContenu = $fichierContenu;

        return $this;
    }

    public function __toString(): string
    {
        return $this->titre ?? '';
    }
}
