<?php

namespace App\Entity;

use App\Repository\CardItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CardItemRepository::class)]
class CardItem implements AccessibleEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $owner = null;

    #[ORM\Column(type: 'boolean')]
    private bool $public = false;

    #[ORM\ManyToMany(targetEntity: User::class)]
    private Collection $writers;

    public function __construct()
    {
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
}
