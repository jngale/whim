<?php
declare(strict_types=1);

abstract class WordPressType extends ProjectType {

    public static function projectCreator(string $name, array $metadata): ProjectCreatorInterface {
        return WordPressProjectCreator::load($name, $metadata);
    }

    public static function isWordPressType(): bool {
        return true;
    }

    public static function getProjectButtons(Project $project): array {
        $buttons = parent::getProjectButtons($project);
        $name = $project->get('name');
        $domain = $project->get('domain');

        // Editing group
        $buttons['editing'][] = static::buttonLink('Open wp-admin', "https://{$domain}.dev.local/wp-admin");
        $buttons['editing'][] = static::buttonLink('View Site', "https://{$domain}.dev.local");
        $buttons['editing'][] = static::buttonAction('Import Site', "importProject('$name')");

        // Deployment group
        $buttons['deployment'][] = static::buttonAction('Stage Site', "stageProject('$name')");
        $buttons['deployment'][] = static::buttonLink('View Staged Site', "https://{$domain}.stage.local");
        $buttons['deployment'][] = static::buttonAction('Publish Site', "publishProject('$name')");

        return $buttons;
    }
    
    


    public function editSiteUrl(): string {
        return "https://{$this->project->domain}.dev.local/wp-admin";
    }

    public function viewSiteUrl(): string {
        return "https://{$this->project->domain}.dev.local";
    }

    public function stagedSiteUrl(): string {
        return "https://{$this->project->domain}.stage.local";
    }



    public function configureProject($project): void {
        parent::configureProject($project); // General setup (vhost + permissions)
    
        syncWpSiteUrls($project); // Specific to WordPress
    }
    
    public function deployProject(Project $project): string {
        echo "Deploy not yet implemented.\n";
    }

    public function importProject(Project $project): void {
        // $this->importSite($project);
        // $this->importDatabase($project);
    }

    public function stageProject(Project $project): string {
        // No-op
    }


    public function importSite(Project $project): void {
        echo "[WordPress] Importing remote files for {$this->name}\n";
        // Future remote file import logic using ssh/scp will go here
    }

    public function importDatabase(Project $project): void {
        echo "[WordPress] Importing remote files for {$this->name}\n";
        // Future remote file import logic using ssh/scp will go here
    }    

    public function stageSite(): bool {
        $source = $this->project->getPath();
        $target = STAGING_ROOT . $this->project->name;
        return shell_exec("rsync -a --delete $source/ $target/") !== null;
    }


}
