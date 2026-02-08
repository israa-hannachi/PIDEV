<?php

namespace App\Entity;

use App\Repository\SponsorRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SponsorRepository::class)]
#[ORM\Table(name: 'sponsors')]
#[ORM\HasLifecycleCallbacks]
class Sponsor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le nom du sponsor est obligatoire")]
    #[Assert\Length(min: 2, max: 100, minMessage: "Le nom doit faire au moins 2 caractères")]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: "L'URL du logo n'est pas valide")]
    private ?string $logo = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: "L'URL du site web n'est pas valide")]
    private ?string $siteWeb = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le type de sponsor est obligatoire")]
    #[Assert\Choice(
        choices: ['principal', 'gold', 'silver', 'bronze', 'partenaire'],
        message: "Le type sélectionné n'est pas valide"
    )]
    private ?string $type = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: "Le montant du sponsoring est obligatoire")]
    #[Assert\Positive(message: "Le montant doit être positif")]
    private ?string $montant = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\GreaterThan(propertyPath: "dateDebut", message: "La date de fin doit être postérieure à la date de début")]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le statut est obligatoire")]
    #[Assert\Choice(
        choices: ['actif', 'inactif', 'en_attente', 'expiré'],
        message: "Le statut sélectionné n'est pas valide"
    )]
    private ?string $statut = 'actif';

    #[ORM\ManyToOne(inversedBy: 'sponsors')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $contactPersonne = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas valide")]
    private ?string $contactEmail = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $contactTelephone = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\PrePersist]
    public function setDateCreationValue(): void
    {
        if ($this->dateCreation === null) {
            $this->dateCreation = new \DateTime();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

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

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getSiteWeb(): ?string
    {
        return $this->siteWeb;
    }

    public function setSiteWeb(?string $siteWeb): static
    {
        $this->siteWeb = $siteWeb;

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

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(string $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

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

    public function getContactPersonne(): ?string
    {
        return $this->contactPersonne;
    }

    public function setContactPersonne(?string $contactPersonne): static
    {
        $this->contactPersonne = $contactPersonne;

        return $this;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(?string $contactEmail): static
    {
        $this->contactEmail = $contactEmail;

        return $this;
    }

    public function getContactTelephone(): ?string
    {
        return $this->contactTelephone;
    }

    public function setContactTelephone(?string $contactTelephone): static
    {
        $this->contactTelephone = $contactTelephone;

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
}