<?php
declare(strict_types=1);

class ProjectSettingsCard extends MetadataAbstractCard {
    protected string $title = 'Project Settings';

    public function shouldDisplay(): bool {
        return true;
    }

    public function render(): string {
        $output = '<div class="field-grid">';

        $keys = [
            'name',
            'domain',
            'type',
            'owner',
            'active',
            'backup_path',
            'hosting_provider',
            'last_deployed',
            'deploy_exclude'
        ];

        
        foreach ($keys as $key) {
            if (!isset($this->template[$key])) {
                continue;
            }
            $config = $this->template[$key];
            $field = $this->renderField($key, $config);
            $output .= "<div class='field-item'>{$field}</div>";
        }

        $output .= '</div>';
        return $this->wrap($output);
    }
}
