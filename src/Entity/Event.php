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
    #[Assert\Length(min: 3, minMessage: "La description doit faire au moins {{ limit }} caractères")]
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

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: AIPrediction::class, orphanRemoval: true)]
    private Collection $aiPredictions;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: EventChat::class, orphanRemoval: true)]
    private Collection $eventChats;

    #[ORM\OneToOne(mappedBy: 'event', targetEntity: EventPoster::class, orphanRemoval: true)]
    private ?EventPoster $poster = null;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: RecommendationCache::class, orphanRemoval: true)]
    private Collection $recommendationCaches;

    #[ORM\Column(length: 50, options: ['default' => 'UTC'])]
    private ?string $timeZone = 'UTC';

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isRecurring = false;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $recurrenceFrequency = null; // DAILY, WEEKLY, MONTHLY, YEARLY

    #[ORM\Column(nullable: true)]
    private ?int $recurrenceCount = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $organizerEmail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $targetAudience = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $requiredLevel = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $tags = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $attendeesEmails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notesInterne = null;

    public function __construct()
    {
        $this->registrations = new ArrayCollection();
        $this->sponsors = new ArrayCollection();
        $this->ratings = new ArrayCollection();
        $this->aiPredictions = new ArrayCollection();
        $this->eventChats = new ArrayCollection();
        $this->recommendationCaches = new ArrayCollection();
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

    /**
     * @return Collection<int, AIPrediction>
     */
    public function getAiPredictions(): Collection
    {
        return $this->aiPredictions;
    }

    public function addAiPrediction(AIPrediction $aiPrediction): static
    {
        if (!$this->aiPredictions->contains($aiPrediction)) {
            $this->aiPredictions->add($aiPrediction);
            $aiPrediction->setEvent($this);
        }

        return $this;
    }

    public function removeAiPrediction(AIPrediction $aiPrediction): static
    {
        if ($this->aiPredictions->removeElement($aiPrediction)) {
            if ($aiPrediction->getEvent() === $this) {
                $aiPrediction->setEvent(null);
            }
        }

        return $this;
    }

    // Calendar/iCal related getters and setters

    public function getTimeZone(): ?string
    {
        return $this->timeZone;
    }

    public function setTimeZone(?string $timeZone): static
    {
        $this->timeZone = $timeZone ?? 'UTC';
        return $this;
    }

    public function isRecurring(): ?bool
    {
        return $this->isRecurring;
    }

    public function setIsRecurring(?bool $isRecurring): static
    {
        $this->isRecurring = $isRecurring;
        return $this;
    }

    public function getRecurrenceFrequency(): ?string
    {
        return $this->recurrenceFrequency;
    }

    public function setRecurrenceFrequency(?string $recurrenceFrequency): static
    {
        $this->recurrenceFrequency = $recurrenceFrequency;
        return $this;
    }

    public function getRecurrenceCount(): ?int
    {
        return $this->recurrenceCount;
    }

    public function setRecurrenceCount(?int $recurrenceCount): static
    {
        $this->recurrenceCount = $recurrenceCount;
        return $this;
    }

    public function getAttendeesEmails(): ?string
    {
        return $this->attendeesEmails;
    }

    public function setAttendeesEmails(?string $attendeesEmails): static
    {
        $this->attendeesEmails = $attendeesEmails;
        return $this;
    }

    public function getNotesInterne(): ?string
    {
        return $this->notesInterne;
    }

    public function setNotesInterne(?string $notesInterne): static
    {
        $this->notesInterne = $notesInterne;
        return $this;
    }

    public function getAttendeesEmailsAsArray(): array
    {
        if (!$this->attendeesEmails) {
            return [];
        }
        // Try to parse as JSON first
        if (str_starts_with($this->attendeesEmails, '[')) {
            return json_decode($this->attendeesEmails, true) ?? [];
        }
        // Otherwise split by comma
        return array_filter(array_map('trim', explode(',', $this->attendeesEmails)));
    }

    public function getOrganizerEmail(): ?string
    {
        return $this->organizerEmail;
    }

    public function setOrganizerEmail(?string $organizerEmail): static
    {
        $this->organizerEmail = $organizerEmail;
        return $this;
    }

    public function getIcalId(): ?string
    {
        return $this->icalId;
    }

    public function setIcalId(?string $icalId): static
    {
        $this->icalId = $icalId;
        return $this;
    }

    /**
     * @return Collection<int, EventChat>
     */
    public function getEventChats(): Collection
    {
        return $this->eventChats;
    }

    public function addEventChat(EventChat $eventChat): static
    {
        if (!$this->eventChats->contains($eventChat)) {
            $this->eventChats->add($eventChat);
            $eventChat->setEvent($this);
        }

        return $this;
    }

    public function removeEventChat(EventChat $eventChat): static
    {
        if ($this->eventChats->removeElement($eventChat)) {
            if ($eventChat->getEvent() === $this) {
                $eventChat->setEvent(null);
            }
        }

        return $this;
    }

    public function getPoster(): ?EventPoster
    {
        return $this->poster;
    }

    public function setPoster(?EventPoster $poster): static
    {
        $this->poster = $poster;
        return $this;
    }

    public function getTargetAudience(): ?string
    {
        return $this->targetAudience;
    }

    public function setTargetAudience(?string $targetAudience): static
    {
        $this->targetAudience = $targetAudience;
        return $this;
    }

    public function getRequiredLevel(): ?string
    {
        return $this->requiredLevel;
    }

    public function setRequiredLevel(?string $requiredLevel): static
    {
        $this->requiredLevel = $requiredLevel;
        return $this;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    /**
     * @return Collection<int, RecommendationCache>
     */
    public function getRecommendationCaches(): Collection
    {
        return $this->recommendationCaches;
    }

    public function addRecommendationCache(RecommendationCache $recommendationCache): static
    {
        if (!$this->recommendationCaches->contains($recommendationCache)) {
            $this->recommendationCaches->add($recommendationCache);
            $recommendationCache->setEvent($this);
        }

        return $this;
    }

    public function removeRecommendationCache(RecommendationCache $recommendationCache): static
    {
        if ($this->recommendationCaches->removeElement($recommendationCache)) {
            // set the owning side to null (unless already changed)
            if ($recommendationCache->getEvent() === $this) {
                $recommendationCache->setEvent(null);
            }
        }

        return $this;
    }
}
