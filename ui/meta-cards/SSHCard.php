<?php
declare(strict_types=1);


class SSHCard extends MetadataAbstractCard {
    protected string $title = 'SSH Settings';

    public function shouldDisplay(): bool {
        $projectName = $this->project->getProjectName();
        $typeName = $this->project->getProjectTypeName();
        $displayMe = ($typeName === 'WordPressSSHType');
        error_log("[JG SSHCard::shouldDisplay] Project: $projectName - $typeName - $displayMe");
        return $displayMe;
    }

    public function keys(): array {
        return [
            'ssh_host',
            'ssh_user',
            'ssh_key_path'
        ];
    }
}
