<?php

namespace App\Infrastructure\Services;

use App\Domain\Interfaces\StorageServiceInterface;

class LocalStorageCoverService implements StorageServiceInterface
{
    private string $uploadDir;
    private string $publicUrlPrefix;

    public function __construct()
    {
        $this->uploadDir = __DIR__ . '/../../../public/storage/epub/';
        $this->publicUrlPrefix = '/storage/epub/';
        if (!is_dir($this->uploadDir)) mkdir($this->uploadDir, 0755, true);
    }

    public function saveFromUpload(array $file): string
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new \InvalidArgumentException('Invalid cover image type');
        }
        if ($file['size'] > 5 * 1024 * 1024) { // 5 MB
            throw new \InvalidArgumentException('Cover too large');
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('cover_') . '.' . $ext;
        $dest = $this->uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new \RuntimeException('Failed to save cover');
        }

        return $this->publicUrlPrefix . $filename;
    }

    public function delete(string $url): void
    {
        $path = $this->uploadDir . basename($url);
        if (file_exists($path)) unlink($path);
    }
}