<?php
declare(strict_types=1);

class PluginType extends WordPressType {

    public static function load(?Project $project = null): static {
        return parent::load($project);
    }

    public static function projectCreator(string $name, array $metadata): ProjectCreatorInterface {
        return PluginProjectCreator::load($name, $metadata);
    }

    protected static function supportsVSCode(): bool {
        return true;
    }

    public static function getProjectButtons(Project $project): array {
        $buttons = parent::getProjectButtons($project); // ✅ pass $project
        $name = $project->get('name');

        // ✅ Remove inherited 'Deploy' button
        $buttons['deployment'] = array_filter(
            $buttons['deployment'],
            fn($btn) => strpos($btn, 'Deploy') === false
        );

        // ✅ Add custom deploy
        $buttons['deployment'][] = static::buttonAction('Deploy to Project', "deployPluginToProject('$name')");

        return $buttons;
    }
  
    public function getVSCodePath(Project $project): string {
        $path = $project->getPath() . '/wp-content/plugins/' . $project->name;
    
        if (is_dir($path)) {
            return $path;
        }
    
        error_log("[PluginType ProjectType] ⚠️ Plugin directory missing, falling back to project root: {$project->getFullPath()}");
        return $project->getFullPath();
    }
    
    public function importProject(Project $project): void {
        // No-op
    }

    public function stageProject(Project $project): string {
        // No-op
    }

    public function deployProject(Project $project): string {
        echo "Deploy not yet implemented.\n";
    }








    public function getDeployCardHtml(): string {
        $name = $this->project->get('name');
        $lastDeployed = $this->project->get('last_deployed');
        $type = get_class($this); // dynamically determine project type
    
        return <<<HTML
        <div class='card'>
            <strong>$name</strong><br>
            <em>Type: $type</em><br>
            Last deployed: $lastDeployed
            <div class='buttons'>
                <button onclick="runProjectAction('$name', 'deploy')">Deploy</button>
                <button onclick="runProjectAction('$name', 'backup')">Backup</button>
                <button onclick="runProjectAction('$name', 'restore')">Restore</button>
            </div>
        </div>
        HTML;
    }   
}

