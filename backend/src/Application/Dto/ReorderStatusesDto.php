<?php

namespace App\Application\Dto;

readonly class ReorderStatusesDto
{
    public array $items;

    /**
     * @param array<int, array{id: int, position: int}> $items
     */
    public function __construct(
        array $items
    ) {
        // Валидация и преобразование за один проход
        $validated = [];
        foreach ($items as $item) {
            if (!isset($item['id']) || !isset($item['position'])) {
                throw new \InvalidArgumentException('Each item must have "id" and "position"');
            }
            // Опционально: проверка типов
            if (!is_int($item['id']) || !is_int($item['position'])) {
                throw new \InvalidArgumentException('id and position must be integers');
            }
            $validated[$item['id']] = $item;
        }

        $this->items = $validated; // ← прямое присваивание — разрешено
    }
}