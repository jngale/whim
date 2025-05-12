<?php
declare(strict_types=1);

class WordPressProjectCreator extends ProjectCreator {
    public function gitignoreTemplate(): string {
        return 'php'; // matches .templates/gitignore-wordpress
    }

    public function postCreate(): void {
        WPInstaller::load($this->name, $this->projectPath);
        $pluginDir = $this->projectPath . "wp-content/plugins/{$this->name}";
        if (!is_dir($pluginDir)) {
            mkdir($pluginDir, 0775, true);
            file_put_contents(
                "$pluginDir/{$this->name}.php",
                "<?php\n// {$this->name} plugin entry point\n"
            );
        }
    }
}