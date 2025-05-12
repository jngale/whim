<?php
declare(strict_types=1);


class FTPCard extends MetadataAbstractCard {
    protected string $title = 'SSH Settings';

    public function shouldDisplay(): bool {
        $projectName = $this->project->getProjectName();
        $typeName = $this->project->getProjectTypeName();
        $displayMe = ($typeName === 'WordPressFTPType');
        error_log("[JG SSHCard::shouldDisplay] Project: $projectName - $typeName - $displayMe");
        return $displayMe;
    }

    public function keys(): array {
        return [
            'ftp_host',
            'ftp_user',
            'ftp_pass'
        ];
    }
}
