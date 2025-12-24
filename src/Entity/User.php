<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const STATUS_UNVERIFIED = 'unverified';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_BLOCKED = 'blocked';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
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

    public function setPassword(string $password): static
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
    public function eraseCredentials(): void
    {}

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

}

