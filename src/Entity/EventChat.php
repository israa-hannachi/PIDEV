<?php

namespace App\Entity;

use App\Repository\EventChatRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventChatRepository::class)]
#[ORM\Table(name: 'event_chats')]
#[ORM\HasLifecycleCallbacks]
class EventChat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'eventChats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\Column(length: 50)]
    private ?string $sender = 'user'; // 'user', 'ai_assistant', 'system'

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Message cannot be empty")]
    private ?string $message = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(length: 50, options: ['default' => 'public'])]
    private ?string $visibility = 'public'; // 'public', 'private'

    #[ORM\Column(nullable: true)]
    private ?int $likes = 0;

    #[ORM\Column(type: 'json', nullable: true)]
    private array $metadata = []; // Store AI model used, response time, etc.

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isAiGenerated = false;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTime();
        }
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    // Getters and Setters

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getSender(): ?string
    {
        return $this->sender;
    }

    public function setSender(string $sender): static
    {
        $this->sender = $sender;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getVisibility(): ?string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): static
    {
        $this->visibility = $visibility;
        return $this;
    }

    public function getLikes(): ?int
    {
        return $this->likes;
    }

    public function setLikes(?int $likes): static
    {
        $this->likes = $likes;
        return $this;
    }

    public function addLike(): static
    {
        $this->likes = ($this->likes ?? 0) + 1;
        return $this;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata): static
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function getIsAiGenerated(): ?bool
    {
        return $this->isAiGenerated;
    }

    public function setIsAiGenerated(bool $isAiGenerated): static
    {
        $this->isAiGenerated = $isAiGenerated;
        return $this;
    }

    public function getSenderDisplayName(): string
    {
        return match ($this->sender) {
            'ai_assistant' => '🤖 AI Assistant',
            'system' => '⚙️ System',
            default => $this->user?->getFirstName() . ' ' . $this->user?->getLastName() ?: 'Anonymous',
        };
    }

    public function isFromUser(): bool
    {
        return $this->sender === 'user';
    }

    public function isFromAI(): bool
    {
        return $this->sender === 'ai_assistant';
    }

    public function isSystem(): bool
    {
        return $this->sender === 'system';
    }
}
