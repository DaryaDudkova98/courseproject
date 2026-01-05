<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;

interface AccessibleEntity
{
    public function getOwnerSafe(): ?User;
    public function getOwner(): ?User;
    public function isPublic(): bool;
    public function getWriters(): Collection;
}