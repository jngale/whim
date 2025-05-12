<?php
declare(strict_types=1);

abstract class ProjectCreator implements ProjectCreatorInterface {
    protected Project $project;
    protected string $name;
    protected array $metadata;
    protected string $projectPath;
    protected string $gitUserName;
    protected string $gitUserEmail;
    protected string $logPath;

    abstract protected function gitignoreTemplate(): string;

    public static function load(string $name, array $metadata): static {
        $instance = new static();
        $instance->name = $name;
        $instance->project = Project::loadFromMetadata($name, $metadata);
        $instance->metadata = $metadata;
        $instance->projectPath = PROJECTS_ROOT . $name . '/';
        $instance->gitUserName = $metadata['git_name'] ?? 'www-data';
        $instance->gitUserEmail = $metadata['git_email'] ?? 'www-data@localhost';
        $instance->logPath = $instance->projectPath . 'create.log';

        return $instance;
    }

    public function getProject(): Project {
        return $this->project;
    }
    
    
    public function create(): void {
        assertNotInsideWhim($this->projectPath);
        safeCreateDirectory($this->projectPath);
        $this->project->saveToDatabase();
        $this->postCreate();
    }

    protected function postCreate(): void {
        // Placeholder for subclasses
    }

    
}
