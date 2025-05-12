<?php
declare(strict_types=1);

class WordPressSSHType extends WordPressType {

    public function stageProject(Project $project): string {
        // No-op
    }

    public function deployProject(Project $project): string {
        echo "Deploy not yet implemented.\n";
    }

    public function importProject(Project $project): void {
        $importer = WordPressSSHImporter::load($project);
        $importer->importSite();
        $importer->importDatabase();
    }

    public function importSite(Project $project): void {
        $importer = WordPressSSHImporter::load($project);
        $importer->importSite();
    }
    
    public function importDatabase(Project $project): void {
        $importer = WordPressSSHImporter::load($project);
        $importer->importDatabase();
    }
    
    
    
}
