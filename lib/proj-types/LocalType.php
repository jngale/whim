<?php
declare(strict_types=1);

class LocalType extends ProjectType {

    public static function getProjectButtons(Project $project): array {
        $buttons = parent::getProjectButtons($project);
        $name = $project->get('name');

        $buttons['deployment'][] = static::buttonAction('Deploy', "deployProject('$name')");

        return $buttons;
    }
    

    public static function projectCreator(string $name, array $metadata): ProjectCreatorInterface {
        return LocalProjectCreator::load($name, $metadata);
    }

    public function importProject(Project $project): void {
        // No-op
    }

    public function stageProject(Project $project): string {
        // No-op
    }
    
    public function deployProject(Project $project): string {
        $output = [];
    
        $projectName = $project->getProjectName();
        $sourceDir = PROJECTS_ROOT . $projectName;
        $targetDir = HTML_ROOT . $projectName;
    
        $output[] = "[Deploy] Starting deployment for project '$projectName'";
    
        if (!is_dir($sourceDir)) {
            $output[] = "[Deploy] ❌ Source directory not found: $sourceDir";
            return implode("\n", $output);
        }
    
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
            $output[] = "[Deploy] Created target directory: $targetDir";
        }
    
        // Backup
        $output[] = "[Deploy] Backing up existing project...";
        $output[] = $this->backupProject($project);
    
        // Exclude files during deploy (optional)
        $exclude = $project->get('deploy_exclude') ?? [];
    
        // Sync target and copy files
        $output[] = "[Deploy] Cleaning up target...";
        $this->syncCleanup($sourceDir, $targetDir);
    
        $output[] = "[Deploy] Copying project files...";
        $this->copyDirectory($sourceDir, $targetDir, $exclude);
    
        $this->configureProject($project);
        $output[] = "[Deploy] Permissions fixed.";
    
        $project->set('last_deployed', date('Y-m-d H:i:s'));
        $project->saveToDatabase();
    
        $output[] = "[Deploy] Updated deployment timestamp in database.";
        $output[] = "[Deploy] ✅ Deployment complete for '$projectName'.";
    
        return implode("\n", $output);
    }
  
}
