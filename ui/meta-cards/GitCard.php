<?php
declare(strict_types=1);


class GitCard extends MetadataAbstractCard {
    protected string $title = 'SSH Settings';

    public function shouldDisplay(): bool {
        return true;
    }

    public function keys(): array {
        return [
            'git_repo_url',
        ];
    }
}
