<?php

namespace App\Entity;

use App\Repository\ForumRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ForumRepository::class)]
class Forum
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre est obligatoire.")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le titre doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le titre ne doit pas dépasser {{ limit }} caractères."
    )]
    private ?string $titre = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'integer')]
    private ?int $userId = null;

    #[ORM\Column(length: 255)]
    private ?string $createdBy = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isBlocked = false;

    #[ORM\OneToMany(mappedBy: 'forum', targetEntity: Message::class, orphanRemoval: true)]
    private Collection $messages;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->messages = new ArrayCollection();
    }

    // ================= GETTERS & SETTERS =================

    public function getId(): ?int { return $this->id; }

    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(string $titre): self { $this->titre = $titre; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function getUserId(): ?int { return $this->userId; }
    public function setUserId(int $userId): self { $this->userId = $userId; return $this; }

    public function getCreatedBy(): ?string { return $this->createdBy; }
    public function setCreatedBy(string $createdBy): self { $this->createdBy = $createdBy; return $this; }

    public function isBlocked(): bool { return $this->isBlocked; }
    public function setIsBlocked(bool $isBlocked): self { $this->isBlocked = $isBlocked; return $this; }

    public function getMessages(): Collection { return $this->messages; }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setForum($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            if ($message->getForum() === $this) {
                $message->setForum(null);
            }
        }

        return $this;
    }
}
