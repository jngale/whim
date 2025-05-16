<?php
declare(strict_types=1);

abstract class ProjectType {
    protected ?Project $project;
    protected Importer $importer;
    protected string $name;
    protected string $webRoot;
    protected string $backupPath;

    abstract public static function projectCreator(string $name, array $metadata): ProjectCreatorInterface;
    abstract function importProject(Project $project): void;

    abstract function stageProject(Project $project): string;
    abstract function deployProject(Project $project): string;

    public static function load(?Project $project = null): static {
        $fqcn = static::class;
    
        // Fallback if called as ProjectType::load() directly
        if ($fqcn === ProjectType::class) {
            $type = $project?->get('type') ?? 'LocalType';
            $fqcn = class_exists($type) ? $type : 'NullProjectType';
            if (!class_exists($fqcn)) {
                error_log("[WHIM] Type class not found: $fqcn. Falling back to NullProjectType.");
                $fqcn = 'NullProjectType';
            }
    
            /** @var static $instance */
            $instance = new $fqcn();
        } else {
            /** @var static $instance */
            $instance = new static();
        }
    
        $instance->project = $project;
    
        if ($project) {
            $instance->name = $project->get('name');
            $instance->backupPath = BACKUP_ROOT . $instance->name;
            $instance->webRoot = '/var/www/html/' . $instance->name;
        }
    
        return $instance;
    }

    public static function fromTypeName(string $typeName, ?Project $project = null): ProjectType {
        if (!class_exists($typeName)) {
            error_log("[WHIM] Unknown type '$typeName'. Falling back to NullProjectType.");
            $typeName = 'NullProjectType';
        }
    
        /** @var ProjectType $instance */
        $instance = new $typeName();
    
        return $instance;
    }

    public static function getProjectButtons(Project $project): array {
        $name = $project->get('name');
        $buttons = [
            'editing' => [],
            'deployment' => [],
        ];

        if (static::supportsVSCode()) {
            $buttons['editing'][] = static::buttonAction('Open in VS Code', "openVSCode('$name')");
        }

        $buttons['editing'][] = static::buttonAction('Configure Project', "configureProject('$name')");
        $buttons['deployment'][] = static::buttonAction('Backup', "backupProject('$name')");
        $buttons['deployment'][] = static::buttonAction('Restore', "restoreProject('$name')");

        return $buttons;
    }

    protected static function buttonLink(string $label, string $url, bool $newTab = true): string {
        $target = $newTab ? "_blank" : "_self";
        return "<button onclick=\"window.open('{$url}', '{$target}')\">{$label}</button>";
    }
    
    protected static function buttonAction(string $label, string $jsFunction): string {
        return "<button onclick=\"{$jsFunction}\">{$label}</button>";
    }

    protected static function supportsVSCode(): bool {
        return false;
    }  
    

    public static function concreteProjectTypes(): array {
        $types = [];
        $typeDir = TYPES_DIR;
    
        foreach (glob($typeDir . "*.php") as $file) {
            $className = basename($file, ".php");
    
            require_once $file; // load it manually — we disabled namespaces
    
            if (!class_exists($className)) {
                continue;
            }
    
            // 🧠 Skip abstract classes
            $ref = new ReflectionClass($className);
            if ($ref->isAbstract()) {
                continue;
            }
    
            if (!is_subclass_of($className, self::class)) {
                continue;
            }
    
            $types[] = $className;
        }
    
        return $types;
    }
    

    // geters and setters

    public static function getDisplayName(): string {
        return get_called_class();  //(new \ReflectionClass($this))->getShortName();
    }

    public function getName(): string {
        return get_class($this);
    }

    public function getVSCodePath(Project $project): string {
        return $project->getPath(); // fallback = root
    }
    
    public function getCreator(string $name, array $metadata): ProjectCreator {
        return $this->getCreatorClass::load($name, $metadata);
    }

    public function getProjectCard(): string {
        $buttonsByGroup = $this->getProjectButtons();
    
        $html = "<div class='card'>
            <strong>{$this->project->name}</strong><br>
            <em>Type: {$this->project->get('type')}</em><br>
            Domain: {$this->project->get('domain')}<br>";
    
        foreach ($buttonsByGroup as $group => $buttons) {
            $html .= "<fieldset><legend>" . ucfirst($group) . "</legend>";
            foreach ($buttons as $btn) {
                $html .= $btn;
            }
            $html .= "</fieldset>";
        }
    
        $html .= "</div>";
        return $html;
    }
    
    public static function isSubclassOf(string $parentClass): bool {
        return is_subclass_of(static::class, $parentClass);
    }
 
    public function backupProject(Project $project): string {
        $sourceDir = HTML_ROOT . $project->getProjectName();
        $backupDir = BACKUP_ROOT . $project->getProjectName();
        return $this->copyProject($sourceDir, $backupDir, ['.git'], 'Backup');
    }
    
    public function restoreProject(Project $project): string {
        $backupDir = BACKUP_ROOT . $project->getProjectName();
        $targetDir = HTML_ROOT . $project->getProjectName();
        return $this->copyProject($backupDir, $targetDir, [], 'Restore');
    }
    
    public function copyProject(string $sourceDir, string $targetDir, array $exclude = [], string $label = 'Copy'): string {
        if (!is_dir($sourceDir)) {
            return "Source directory does not exist: $sourceDir";
        }
    
        // Clean target
        if (is_dir($targetDir)) {
            $this->deleteDirectory($targetDir);
        }
    
        // Recreate target
        if (!mkdir($targetDir, 0755, true)) {
            return "Failed to create target directory: $targetDir";
        }
    
        try {
            $this->copyDirectory($sourceDir, $targetDir, $exclude);
        } catch (Throwable $e) {
            return "Exception during copy: " . $e->getMessage();
        }
    
        return "Copied project from $sourceDir to $targetDir";
    }
    
       

    protected function copyDirectory(string $source, string $target, array $exclude = []): void {
        if (!is_dir($target)) {
            mkdir($target, 0755, true);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relPath = substr($item->getPathname(), strlen($source) + 1);

            if (in_array($relPath, $exclude)) {
                continue;
            }

            $targetPath = $target . DIRECTORY_SEPARATOR . $relPath;

            if ($item->isDir()) {
                mkdir($targetPath, 0755, true);
            } else {
                copy($item->getPathname(), $targetPath);
            }
        }
    }

    protected function deleteDirectory(string $path): void {
        if (!is_dir($path)) return;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }
    }

    protected function syncCleanup(string $source, string $target): void {
        if (!is_dir($target)) {
            // Nothing to clean up if target doesn't exist
            return;
        }
    
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($target, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
    
        foreach ($iterator as $file) {
            $relativePath = substr($file->getPathname(), strlen($target) + 1);
            $sourcePath = $source . DIRECTORY_SEPARATOR . $relativePath;
    
            if (!file_exists($sourcePath)) {
                if ($file->isDir()) {
                    rmdir($file->getPathname());
                } else {
                    unlink($file->getPathname());
                }
            }
        }
    }
    
    public function configureProject($project): void {
        $configurator = new ProjectConfigurator($project);
        $output = $configurator->configure();

        foreach ($output as $line) {
            error_log("[ConfigureProject] $line");
        }
    }

    public static function isAbstract(string $className): bool {
        try {
            $ref = new \ReflectionClass($className);
            return $ref->isAbstract();
        } catch (ReflectionException $e) {
            return true; // treat non-existent or broken class as abstract
        }
    }

    public function openVSCode(): void {
        $path = $this->project->getPath();
        shell_exec("code \"$path\" > /dev/null 2>&1 &");
    }

    public static function isWordPressType(): bool {
        return false;
    }

    
}


