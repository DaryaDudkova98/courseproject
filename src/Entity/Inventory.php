<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\InventoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryRepository::class)]
class Inventory implements AccessibleEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

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

    public function __construct()
    {
        $this->writers = new ArrayCollection();
        $this->items = new ArrayCollection();
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
            // set the owning side to null (unless already changed)
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

    public function __toString(): string
    {
        return $this->getName();
    }

}