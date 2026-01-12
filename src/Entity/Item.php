<?php

namespace App\Entity;

use App\Repository\ItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
#[ORM\Table(name: 'item')]
class Item implements AccessibleEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, Like>
     */
    #[ORM\OneToMany(targetEntity: Like::class, mappedBy: 'item')]
    private Collection $likes;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'ownedItems')]
    private ?User $owner = null;

    #[ORM\Column(type: 'boolean')]
    private bool $public = false;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'writableItems')]
    private Collection $writers;

    #[ORM\ManyToOne(targetEntity: Inventory::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Inventory $inventory = null;

    public function __construct()
    {
        $this->likes = new ArrayCollection();
        $this->writers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return Collection<int, Like>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Like $like): static
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->setItem($this);
        }
        return $this;
    }

    public function removeLike(Like $like): static
    {
        if ($this->likes->removeElement($like)) {
            if ($like->getItem() === $this) {
                $like->setItem(null);
            }
        }
        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;
        return $this;
    }

    public function getOwnerSafe(): ?User
    {
        return $this->owner;
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

    public function addWriter(User $user): static
    {
        if (!$this->writers->contains($user)) {
            $this->writers->add($user);
        }
        return $this;
    }

    public function removeWriter(User $user): static
    {
        $this->writers->removeElement($user);
        return $this;
    }

    public function getInventory(): ?Inventory
    {
        return $this->inventory;
    }

    public function setInventory(?Inventory $inventory): static
    {
        $this->inventory = $inventory;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? 'Unnamed Item';
    }
}
