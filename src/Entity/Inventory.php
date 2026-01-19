<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\InventoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: InventoryRepository::class)]
#[ApiResource]
class Inventory implements AccessibleEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'inventories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(type: 'boolean')]
    private bool $public = false;

    #[ORM\ManyToMany(targetEntity: User::class)]
    private Collection $writers;

    #[ORM\OneToMany(targetEntity: Item::class, mappedBy: 'inventory')]
    private Collection $items;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'inventories')]
    #[ORM\JoinTable(name: 'inventory_tags')]
    private Collection $tags;

    #[ORM\Column(type: 'string', length: 255, nullable: true, unique: true)]
    private ?string $apiToken = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $apiTokenGeneratedAt = null;

    public function __construct()
    {
        $this->writers = new ArrayCollection();
        $this->items = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(?string $apiToken): static
    {
        $this->apiToken = $apiToken;
        return $this;
    }

    public function generateApiToken(): static
    {
        $this->apiToken = bin2hex(random_bytes(16));
        $this->apiTokenGeneratedAt = new \DateTime();
        return $this;
    }

    public function isApiTokenVisibleToUser(?UserInterface $user): bool
    {
        if (!$user) {
            return false;
        }

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        if ($user === $this->owner) {
            return true;
        }

        foreach ($this->writers as $writer) {
            if ($writer === $user) {
                return true;
            }
        }

        return false;
    }

    public function getApiTokenGeneratedAt(): ?\DateTimeInterface
    {
        return $this->apiTokenGeneratedAt;
    }

    public function setApiTokenGeneratedAt(?\DateTimeInterface $apiTokenGeneratedAt): static
    {
        $this->apiTokenGeneratedAt = $apiTokenGeneratedAt;
        return $this;
    }

    public function hasApiToken(): bool
    {
        return !empty($this->apiToken);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function getOwnerSafe(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        if ($this->owner !== null && $owner !== null && $this->owner !== $owner) {
            throw new \RuntimeException('Cannot change owner of inventory');
        }

        $this->owner = $owner;
        return $this;
    }

    public function initializeOwner(User $owner): static
    {
        if ($this->owner !== null) {
            throw new \RuntimeException('Owner already initialized');
        }

        $this->owner = $owner;
        return $this;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): static
    {
        $this->public = $public;
        return $this;
    }

    public function getWriters(): Collection
    {
        return $this->writers;
    }

    public function addWriter(User $writer): static
    {
        if (!$this->writers->contains($writer)) {
            $this->writers->add($writer);
        }
        return $this;
    }

    public function removeWriter(User $writer): static
    {
        $this->writers->removeElement($writer);
        return $this;
    }

    /**
     * @return Collection<int, Item>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(Item $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setInventory($this);
        }
        return $this;
    }

    public function removeItem(Item $item): static
    {
        if ($this->items->removeElement($item)) {
            if ($item->getInventory() === $this) {
                $item->setInventory(null);
            }
        }
        return $this;
    }

    public function getName(): string
    {
        return $this->category ? (string)$this->category : 'Uncategorized Inventory';
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getApiTokenGeneratedAtFormatted(): ?string
    {
        if (!$this->apiTokenGeneratedAt) {
            return null;
        }

        return $this->apiTokenGeneratedAt->format('d.m.Y H:i');
    }
}
