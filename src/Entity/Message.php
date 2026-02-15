<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Le contenu du message ne peut pas être vide.")]
    #[Assert\Length(
        min: 5,
        minMessage: "Le contenu du message doit contenir au moins {{ limit }} caractères.",
    )]
    private ?string $contenu = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $datePublication = null;

    #[ORM\ManyToOne(targetEntity: Forum::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le forum est obligatoire.")]
    private ?Forum $forum = null;

    #[ORM\Column(length: 200)]
    #[Assert\Choice(choices: ["Actif", "Inactif"], message: "L'état doit être 'Actif' ou 'Inactif'.")]
    private ?string $etat = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank(message: "L'auteur du message est obligatoire.")]
    #[Assert\Length(
        max: 150,
        maxMessage: "Le nom de l'auteur ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $createdBy = null; // auteur du message

    public function __construct()
    {
        $this->datePublication = new \DateTimeImmutable(); // date automatique
        $this->etat = 'Actif'; // état par défaut
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function getDatePublication(): ?\DateTimeImmutable
    {
        return $this->datePublication;
    }

    public function setDatePublication(\DateTimeImmutable $datePublication): static
    {
        $this->datePublication = $datePublication;
        return $this;
    }

    public function getForum(): ?Forum
    {
        return $this->forum;
    }

    public function setForum(?Forum $forum): static
    {
        $this->forum = $forum;
        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;
        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreatedBy(string $createdBy): static
    {
        $this->createdBy = $createdBy;
        return $this;
    }
}
