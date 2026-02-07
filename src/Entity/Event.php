<?php


namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: 'events')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['titre'], message: 'Un événement avec ce titre existe déjà.')]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 200, unique: true)]
    #[Assert\NotBlank(message: "Le titre est obligatoire")]
    #[Assert\Length(
        min: 3, 
        max: 200, 
        minMessage: "Le titre doit faire au moins {{ limit }} caractères", 
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description est obligatoire")]
    #[Assert\Length(min: 10, minMessage: "La description doit faire au moins {{ limit }} caractères")]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: "La date de début est obligatoire")]
    #[Assert\GreaterThan("today", message: "La date de début doit être future")]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: "La date de fin est obligatoire")]
    #[Assert\GreaterThan(propertyPath: "dateDebut", message: "La date de fin doit être postérieure à la date de début")]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(options: ['default' => 50])]
    #[Assert\Positive(message: "La capacité doit être positive")]
    private ?int $capacite = 50;

    #[ORM\Column(options: ['default' => 0])]
    private ?int $inscrits = 0;

    #[ORM\Column(length: 300, nullable: true)]
    #[Assert\Url(message: "L'URL de l'image n'est pas valide")]
    private ?string $image = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "La catégorie est obligatoire")]
    private ?string $categorie = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2, options: ['default' => 0.00])]
    #[Assert\PositiveOrZero(message: "Le prix ne peut pas être négatif")]
    private ?string $prix = '0.00';

    #[ORM\Column(length: 250)]
    #[Assert\NotBlank(message: "Le lieu est obligatoire")]
    private ?string $lieu = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 6, nullable: true)]
    private ?string $latitude = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 6, nullable: true)]
    private ?string $longitude = null;

    #[ORM\Column(length: 50)]
    private ?string $statut = 'planifié';

    #[ORM\OneToMany(mappedBy: 'evenement', targetEntity: Registration::class)]
    private Collection $registrations;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Sponsor::class)]
    private Collection $sponsors;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Rating::class, orphanRemoval: true)]
    private Collection $ratings;

    public function __construct()
    {
        $this->registrations = new ArrayCollection();
        $this->sponsors = new ArrayCollection();
        $this->ratings = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setDateCreationValue(): void
    {
        if ($this->dateCreation === null) {
            $this->dateCreation = new \DateTime();
        }
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setDefaultValues(): void
    {
        // Définir le statut par défaut
        if ($this->statut === null) {
            $this->statut = 'planifié';
        }
    }

   // Getters and Setters

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

public function setDescription(string $description): static
{
    $this->description = $description;

    return $this;
}

public function getDateCreation(): ?\DateTimeInterface
{
    return $this->dateCreation;
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

public function getCapacite(): ?int
{
    return $this->capacite;
}

public function setCapacite(int $capacite): static
{
    $this->capacite = $capacite;

    return $this;
}

public function getInscrits(): ?int
{
    return $this->inscrits;
}

public function setInscrits(int $inscrits): static
{
    $this->inscrits = $inscrits;

    return $this;
}

public function getImage(): ?string
{
    return $this->image;
}

public function setImage(?string $image): static
{
    $this->image = $image;

    return $this;
}

public function getCategorie(): ?string
{
    return $this->categorie;
}

public function setCategorie(string $categorie): static
{
    $this->categorie = $categorie;

    return $this;
}

public function getPrix(): ?string
{
    return $this->prix;
}

public function setPrix(string $prix): static
{
    $this->prix = $prix;

    return $this;
}

public function getLieu(): ?string
{
    return $this->lieu;
}

public function setLieu(string $lieu): static
{
    $this->lieu = $lieu;

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

    /**
     * @return Collection<int, Registration>
     */
    public function getRegistrations(): Collection
    {
        return $this->registrations;
    }

    public function addRegistration(Registration $registration): static
    {
        if (!$this->registrations->contains($registration)) {
            $this->registrations->add($registration);
            $registration->setEvenement($this);
        }

        return $this;
    }

    public function removeRegistration(Registration $registration): static
    {
        if ($this->registrations->removeElement($registration)) {
            // set the owning side to null (unless already changed)
            if ($registration->getEvenement() === $this) {
                $registration->setEvenement(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Sponsor>
     */
    public function getSponsors(): Collection
    {
        return $this->sponsors;
    }

    public function addSponsor(Sponsor $sponsor): static
    {
        if (!$this->sponsors->contains($sponsor)) {
            $this->sponsors->add($sponsor);
            $sponsor->setEvent($this);
        }

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @return Collection<int, Rating>
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function addRating(Rating $rating): static
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings->add($rating);
            $rating->setEvent($this);
        }

        return $this;
    }

    public function removeRating(Rating $rating): static
    {
        if ($this->ratings->removeElement($rating)) {
            // set the owning side to null (unless already changed)
            if ($rating->getEvent() === $this) {
                $rating->setEvent(null);
            }
        }

        return $this;
    }
}
