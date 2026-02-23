<?php
// src/Entity/Registration.php

namespace App\Entity;

use App\Repository\RegistrationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RegistrationRepository::class)]
#[ORM\Table(name: 'registrations')]
#[ORM\HasLifecycleCallbacks]
class Registration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'registrations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $evenement = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le nom du participant est obligatoire")]
    #[Assert\Length(min: 2, max: 100, minMessage: "Le nom doit faire au moins 2 caractères")]
    private ?string $visitorName = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: "L'email du participant est obligatoire")]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas valide")]
    private ?string $visitorEmail = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeInterface $dateInscription = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le statut de l'inscription est obligatoire")]
    #[Assert\Choice(
        choices: ['en_attente', 'confirmé', 'annulé', 'refusé', 'inscrit'], 
        message: "Le statut sélectionné n'est pas valide"
    )]
    private ?string $statut = null;

    #[ORM\Column(options: ['default' => false])]
    #[Assert\Type(type: 'bool', message: "La présence doit être un booléen")]
    private ?bool $presence = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 1000, maxMessage: "Les notes ne peuvent pas dépasser {{ limit }} caractères")]
    private ?string $notes = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $paymentMethod = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paymentToken = null;

    public function __construct()
    {
        $this->dateInscription = new \DateTime();
        $this->statut = 'en_attente';
    }

    #[ORM\PrePersist]
    public function setDateInscriptionValue(): void
    {
        if ($this->dateInscription === null) {
            $this->dateInscription = new \DateTime();
        }
    }

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvenement(): ?Event
    {
        return $this->evenement;
    }

    public function setEvenement(?Event $evenement): static
    {
        $this->evenement = $evenement;

        return $this;
    }

    public function getVisitorName(): ?string
    {
        return $this->visitorName;
    }

    public function setVisitorName(?string $visitorName): static
    {
        $this->visitorName = $visitorName;

        return $this;
    }

    public function getVisitorEmail(): ?string
    {
        return $this->visitorEmail;
    }

    public function setVisitorEmail(?string $visitorEmail): static
    {
        $this->visitorEmail = $visitorEmail;

        return $this;
    }

    public function getDateInscription(): ?\DateTimeInterface
    {
        return $this->dateInscription;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getPresence(): ?bool
    {
        return $this->presence;
    }

    public function setPresence(bool $presence): static
    {
        $this->presence = $presence;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?string $paymentMethod): static
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getPaymentToken(): ?string
    {
        return $this->paymentToken;
    }

    public function setPaymentToken(?string $paymentToken): static
    {
        $this->paymentToken = $paymentToken;

        return $this;
    }
}