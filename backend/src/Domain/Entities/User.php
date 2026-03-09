<?php

namespace App\Domain\Entities;

class User
{
    private int $id;
    private string $name;
    private string $email;
    private string $password; // Хэшированный пароль
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;
    private ?string $resetToken = null;
    private ?\DateTimeImmutable $resetTokenExpiry = null;

    public function __construct(
        int $id,
        string $name,
        string $email,
        string $password,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
        ?string $resetToken = null,
        ?\DateTimeImmutable $resetTokenExpiry = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->resetToken = $resetToken;
        $this->resetTokenExpiry = $resetTokenExpiry;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function updateName(string $name): void
    {
        $this->name = $name;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updatePassword(string $password): void
    {
        $this->password = $password;
        $this->updatedAt = new \DateTimeImmutable();
        $this->resetToken = null;
        $this->resetTokenExpiry = null;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): void
    {
        $this->resetToken = $resetToken;
    }

    public function getResetTokenExpiry(): ?\DateTimeImmutable
    {
        return $this->resetTokenExpiry;
    }

    public function setResetTokenExpiry(?\DateTimeImmutable $resetTokenExpiry): void
    {
        $this->resetTokenExpiry = $resetTokenExpiry;
    }

    public function isResetTokenValid(): bool
    {
        if (!$this->resetToken || !$this->resetTokenExpiry) {
            return false;
        }
        return $this->resetTokenExpiry > new \DateTimeImmutable();
    }
}