<?php
declare(strict_types=1);

interface ProjectCreatorInterface {
    public static function load(string $name, array $metadata): static;
    public function create(): void;
}
