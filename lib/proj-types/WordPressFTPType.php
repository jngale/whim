<?php
declare(strict_types=1);

class WordPressFTP extends WordPressType {

    public function stageProject(Project $project): string {
        // No-op
    }

    public function deployProject(Project $project): string {
        echo "Deploy not yet implemented.\n";
    }
    
    public function importFiles(): void {
        echo "[WordPressSSH] Importing remote files for {$this->name}\n";
        // Future remote file import logic using ssh/scp will go here
    }

}
