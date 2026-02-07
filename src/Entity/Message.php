<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\MessageRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Le message ne peut pas être vide.')]
    private ?string $contenu = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $date_creation = null;

    #[ORM\Column(length: 255)]
    private ?string $created_by = null;

    #[ORM\ManyToOne(targetEntity: Forum::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Forum $forum = null;

    public function __construct()
    {
        $this->date_creation = new \DateTime();
    }

    // --- Getters & Setters ---

    public function getId(): ?int { return $this->id; }

    public function getContenu(): ?string { return $this->contenu; }
    public function setContenu(string $contenu): self { $this->contenu = $contenu; return $this; }

    public function getDateCreation(): ?\DateTimeInterface { return $this->date_creation; }
    public function setDateCreation(\DateTimeInterface $date_creation): self { $this->date_creation = $date_creation; return $this; }

    public function getCreatedBy(): ?string { return $this->created_by; }
    public function setCreatedBy(string $created_by): self { $this->created_by = $created_by; return $this; }

    public function getForum(): ?Forum { return $this->forum; }
    public function setForum(?Forum $forum): self { $this->forum = $forum; return $this; }
}
