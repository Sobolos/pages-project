<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Application\Commands\MigrateCommand;

if ($argc < 2) {
    echo "Использование: php bin/migrate.php [up|down]\n";
    exit(1);
}

$direction = $argv[1];
try {
    $command = new MigrateCommand();
    $command->execute($direction);
} catch (\Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
    exit(1);
}