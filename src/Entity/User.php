<?php

namespace App\Entity;

use App\Entity\Item;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const STATUS_UNVERIFIED = 'unverified';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_BLOCKED = 'blocked';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $password = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 20)]
    private ?string $status = self::STATUS_UNVERIFIED;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastSeen = null;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column(length: 5, options: ["default" => "en"])]
    private ?string $preferred_lang = 'en';

    #[ORM\Column(length: 20, options: ["default" => "auto"])]
    private ?string $theme = 'auto';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $googleId = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Item::class)]
    private Collection $ownedItems;

    #[ORM\ManyToMany(targetEntity: Item::class, mappedBy: 'writers')]
    private Collection $writableItems;


    /**
     * @var Collection<int, Like>
     */
    #[ORM\OneToMany(targetEntity: Like::class, mappedBy: 'likedBy')]
    private Collection $item;

    public function __construct()
    {
        $this->item = new ArrayCollection();
        $this->ownedItems = new ArrayCollection();
        $this->writableItems = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        if (!in_array('ROLE_USER', $roles, true)) {
            $roles[] = 'ROLE_USER';
        }

        $roles = array_unique($roles);

        $this->roles = array_values($roles);

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function isUnverified(): bool
    {
        return self::STATUS_UNVERIFIED === $this->status;
    }

    public function isActive(): bool
    {
        return self::STATUS_ACTIVE === $this->status;
    }

    public function isBlocked(): bool
    {
        return self::STATUS_BLOCKED === $this->status;
    }

    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_UNVERIFIED,
            self::STATUS_ACTIVE,
            self::STATUS_BLOCKED,
        ];
    }

    public function getLastSeen(): ?\DateTimeImmutable
    {
        return $this->lastSeen;
    }

    public function setLastSeen(?\DateTimeImmutable $lastSeen): static
    {
        $this->lastSeen = $lastSeen;

        return $this;
    }

    #[\Deprecated]
    public function eraseCredentials(): void {}

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->preferred_lang;
    }

    public function setLocale(string $preferred_lang): static
    {
        $this->preferred_lang = $preferred_lang;

        return $this;
    }

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function setTheme(string $theme): static
    {
        $this->theme = $theme;

        return $this;
    }

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setGoogleId(?string $googleId): static
    {
        $this->googleId = $googleId;

        return $this;
    }

    public function getOwnedItems(): Collection
    {
        return $this->ownedItems;
    }

    public function addOwnedItem(Item $item): self
    {
        if (!$this->ownedItems->contains($item)) {
            $this->ownedItems->add($item);
            $item->setOwner($this);
        }
        return $this;
    }

    public function removeOwnedItem(Item $item): self
    {
        if ($this->ownedItems->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getOwner() === $this) {
                $item->setOwner(null);
            }
        }
        return $this;
    }

    public function getWritableItems(): Collection
    {
        return $this->writableItems;
    }

    public function addWritableItem(Item $item): self
    {
        if (!$this->writableItems->contains($item)) {
            $this->writableItems->add($item);
            $item->addWriter($this);
        }
        return $this;
    }

    public function removeWritableItem(Item $item): self
    {
        if ($this->writableItems->removeElement($item)) {
            $item->removeWriter($this);
        }
        return $this;
    }

    /**
     * @return Collection<int, Like>
     */
    public function getItem(): Collection
    {
        return $this->item;
    }

    public function addItem(Like $item): static
    {
        if (!$this->item->contains($item)) {
            $this->item->add($item);
            $item->setLikedBy($this);
        }

        return $this;
    }

    public function removeItem(Like $item): static
    {
        if ($this->item->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getLikedBy() === $this) {
                $item->setLikedBy(null);
            }
        }

        return $this;
    }

    public function canEditItem(Item $item): bool
    {
        return $this->ownsItem($item) || $this->canWriteItem($item);
    }

    public function ownsItem(Item $item): bool
    {
        return $this === $item->getOwner();
    }

    public function canWriteItem(Item $item): bool
    {
        return $item->getWriters()->contains($this);
    }

    public function __toString(): string
    {
        return $this->email ?? $this->name ?? (string) $this->id;
    }
}
