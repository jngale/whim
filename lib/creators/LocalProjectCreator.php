<?php
declare(strict_types=1);

class LocalProjectCreator extends ProjectCreator {
    protected function gitignoreTemplate(): string {
        return 'php'; // matches .templates/gitignore-php
    }
}
