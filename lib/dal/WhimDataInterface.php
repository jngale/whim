<?php
declare(strict_types=1);

interface WhimDataInterface {
    public function get(string $field, mixed $value, string $table, string $db): ?array;
    public function getAll(string $table, string $db): array;
    public function save(array $data, string $table, string $db): void;
}

