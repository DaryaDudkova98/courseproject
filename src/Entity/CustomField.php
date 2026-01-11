<?php

namespace App\Entity;

use App\Repository\CustomFieldRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CustomFieldRepository::class)]
#[ORM\Table(name: 'custom_fields')]
#[ORM\HasLifecycleCallbacks]
class CustomField
{

    public const TYPE_TEXT_SINGLE = 'text_single';
    public const TYPE_TEXT_MULTI = 'text_multi';
    public const TYPE_NUMBER = 'number';
    public const TYPE_DOCUMENT = 'document';
    public const TYPE_BOOLEAN = 'boolean';
    
    public const AVAILABLE_TYPES = [
        self::TYPE_TEXT_SINGLE,
        self::TYPE_TEXT_MULTI,
        self::TYPE_NUMBER,
        self::TYPE_DOCUMENT,
        self::TYPE_BOOLEAN,
    ];
    
    public const TYPE_LABELS = [
        self::TYPE_TEXT_SINGLE => 'Text field (single line)',
        self::TYPE_TEXT_MULTI => 'Text field (multiple lines)',
        self::TYPE_NUMBER => 'Number field',
        self::TYPE_DOCUMENT => 'Document/Image (link)',
        self::TYPE_BOOLEAN => 'True/False (checkbox)',
    ];
    
    public const TYPE_LIMITS = [
        self::TYPE_TEXT_SINGLE => 3,
        self::TYPE_TEXT_MULTI => 3,
        self::TYPE_NUMBER => 3,
        self::TYPE_DOCUMENT => 3,
        self::TYPE_BOOLEAN => 3,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Field title is required")]
    #[Assert\Length(max: 100, maxMessage: "Title cannot exceed {{ limit }} characters")]
    private ?string $title = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 500, maxMessage: "Description cannot exceed {{ limit }} characters")]
    private ?string $description = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "Field type is required")]
    #[Assert\Choice(
        choices: self::AVAILABLE_TYPES,
        message: "Choose a valid field type"
    )]
    private ?string $type = null;

    #[ORM\Column]
    private bool $showInTable = false;

    #[ORM\Column]
    private int $sortOrder = 0;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateTimestamps(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
        
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    #[ORM\PrePersist]
    public function generateSlug(): void
    {
        if (empty($this->slug) && !empty($this->title)) {
            $slug = mb_strtolower($this->title);
            $slug = preg_replace('/[^a-z0-9а-яё]+/u', '_', $slug);
            $slug = trim($slug, '_');
            $this->slug = 'custom_' . $slug . '_' . substr(md5(uniqid()), 0, 8);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
        return $this;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        if (!in_array($type, self::AVAILABLE_TYPES, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid field type "%s". Allowed types: %s',
                $type,
                implode(', ', self::AVAILABLE_TYPES)
            ));
        }
        
        $this->type = $type;
        return $this;
    }

    public function isShowInTable(): bool
    {
        return $this->showInTable;
    }

    public function setShowInTable(bool $showInTable): static
    {
        $this->showInTable = $showInTable;
        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getTypeLabel(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }
    
    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_TEXT_SINGLE => self::TYPE_LABELS[self::TYPE_TEXT_SINGLE],
            self::TYPE_TEXT_MULTI => self::TYPE_LABELS[self::TYPE_TEXT_MULTI],
            self::TYPE_NUMBER => self::TYPE_LABELS[self::TYPE_NUMBER],
            self::TYPE_DOCUMENT => self::TYPE_LABELS[self::TYPE_DOCUMENT],
            self::TYPE_BOOLEAN => self::TYPE_LABELS[self::TYPE_BOOLEAN],
        ];
    }
    
    public static function getLimits(): array
    {
        return self::TYPE_LIMITS;
    }
    
    public function getLimit(): int
    {
        return self::TYPE_LIMITS[$this->type] ?? 0;
    }

    public function isTextSingle(): bool
    {
        return $this->type === self::TYPE_TEXT_SINGLE;
    }
    
    public function isTextMulti(): bool
    {
        return $this->type === self::TYPE_TEXT_MULTI;
    }
    
    public function isNumber(): bool
    {
        return $this->type === self::TYPE_NUMBER;
    }
    
    public function isDocument(): bool
    {
        return $this->type === self::TYPE_DOCUMENT;
    }
    
    public function isBoolean(): bool
    {
        return $this->type === self::TYPE_BOOLEAN;
    }
    
    public function __toString(): string
    {
        return $this->title ?? '';
    }
}