<?php
declare(strict_types=1);

abstract class Importer {
    protected Project $project;

    public static function load(Project $project): static {
        $instance = new static();
        $instance->project = $project;
        return $instance;
    }

    abstract public function importSite(): void;
    abstract public function importDatabase(): void;
}


