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

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le mode de paiement est obligatoire")]
    #[Assert\Choice(
        choices: ['gratuit', 'carte', 'espèces', 'virement', 'cheque', 'paypal', 'autre'],
        message: "Le mode de paiement sélectionné n'est pas valide"
    )]
    private ?string $modePaiement = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2, options: ['default' => 0.00])]
    #[Assert\NotBlank(message: "Le montant payé est obligatoire")]
    #[Assert\PositiveOrZero(message: "Le montant payé ne peut pas être négatif")]
    #[Assert\LessThanOrEqual(value: 99999.99, message: "Le montant payé est trop élevé")]
    private ?string $montantPaye = '0.00';

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "L'état du paiement est obligatoire")]
    #[Assert\Choice(
        choices: ['non_requis', 'en_attente', 'payé', 'remboursé', 'retard', 'echec'],
        message: "L'état du paiement sélectionné n'est pas valide"
    )]
    private ?string $paiementStatut = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 1000, maxMessage: "Les notes ne peuvent pas dépasser {{ limit }} caractères")]
    private ?string $notes = null;

    public function __construct()
    {
        $this->dateInscription = new \DateTime();
        $this->statut = 'en_attente';
        $this->paiementStatut = 'en_attente';
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

public function setVisitorName(string $visitorName): static
{
    $this->visitorName = $visitorName;

    return $this;
}

public function getVisitorEmail(): ?string
{
    return $this->visitorEmail;
}

public function setVisitorEmail(string $visitorEmail): static
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

public function setStatut(string $statut): static
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

public function getModePaiement(): ?string
{
    return $this->modePaiement;
}

public function setModePaiement(string $modePaiement): static
{
    $this->modePaiement = $modePaiement;

    return $this;
}

public function getMontantPaye(): ?string
{
    return $this->montantPaye;
}

public function setMontantPaye(string $montantPaye): static
{
    $this->montantPaye = $montantPaye;

    return $this;
}

public function getPaiementStatut(): ?string
{
    return $this->paiementStatut;
}

public function setPaiementStatut(string $paiementStatut): static
{
    $this->paiementStatut = $paiementStatut;

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
}