<?php
declare(strict_types=1);

class Project {
    private array $metadata = [];

    // Creation

    public static function fromArray(array $data): self {
        $project = new self();
    
        // Merge with template to ensure all expected keys exist
        $template = json_decode(file_get_contents(REF_META_FILE), true);
        $merged = [];
    
        foreach ($template as $key => $info) {
            $merged[$key] = $data[$key] ?? match ($info['type']) {
                'boolean' => false,
                'array'   => [],
                'date'    => '',
                default   => ''
            };
        }
    
        $project->metadata = $merged;
    
        return $project;
    }
    
    public static function getByName(string $name): ?Project {
        return ProjectDAO::getByName($name);
    }
    
    public static function getAll(): array {
        return ProjectDAO::getAll();
    }
    
    public static function getActive(): array {
        return array_filter(
            self::getAll(), 
            fn($p) => $p->isActive()
        );
    }


    // Boolean Tests

    public function isActive(): bool {
        return (bool) $this->get("active");
    }
    
    public function isWhim(): bool {
        return $this->getName() === 'whim';
    }

    
    // getters / setters

    public function getProjectName(): string {
        return (string) $this->get('name');
    }
    
    public function getProjectTypeName(): string {
        return (string) $this->get('type');
    }
    
    public function getDevelopmentDirectory(): string {
        return PROJECTS_ROOT . $this->getProjectName();
    }

    public function getProjectType(): ProjectType {
        return ProjectType::fromTypeName($this->getProjectTypeName(), $this);
    }
    
    public function getMetadata(): array {
        return $this->metadata;
    }
 
    public function getPath(): string {
        return $this->getDevelopmentDirectory();
    }

    public function getVSCodePath(): string {
        return $this->getProjectType()->getVSCodePath($this);
    }
    
    public function get(string $key): mixed {
        return array_key_exists($key, $this->metadata) ? $this->metadata[$key] : null;
    }

    public function set(string $key, mixed $value): void {
        $this->metadata[$key] = $value;
    }

    
    // db functionality / metadata

    public function saveToDatabase(): void {
        ProjectDAO::save($this);
    }

    public function toArray(): array {
        return $this->metadata;
    }


    // Redirect to projectType

    public function performAction(string $action): void {
        $type = ProjectType::load($this);
    
        match ($action) {
            'deployProject'     => $type->deployProject($this),
            'backupProject'     => $type->backupProject($this),
            'restoreProject'    => $type->restoreProject($this),
            'stageProject'      => $type->stageProject($this),
            'importProject'     => $type->importProject($this),
            'configureProject'    => $type->configureProject($this),
            default             => throw new \RuntimeException("Unknown action '$action'")
        };
    }
 
    public function configureProject(): void {
        $this->getProjectType()->configureProject($this);
    }

    public function deploy(): void {
        $this->projectType->deployProject($this);
    }

    public function backup(): void {
        $this->projectType->backupProject($this);
    }

    public function restore(): void {
        $this->projectType->restoreProject($this);
    }

    public function getProjectCard(): string {
        return $this->projectType->getProjectCard();
    }
        

}
