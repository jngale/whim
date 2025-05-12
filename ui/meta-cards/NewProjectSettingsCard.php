<?php
declare(strict_types=1);

class NewProjectSettingsCard extends ProjectSettingsCard
{
    public static function load(?Project $project, array $template): static {
        $instance = new static();
        $instance->project = null;          // No associated project yet
        $instance->template = $template;    // Use provided template
        return $instance;
    }
}
