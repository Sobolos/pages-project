<?php

namespace App\Domain\Interfaces;

interface StorageServiceInterface
{
    public function saveFromUpload(array $file): string;
    public function delete(string $url): void;
}