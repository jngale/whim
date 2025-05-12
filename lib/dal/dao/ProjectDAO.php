<?php
declare(strict_types=1);

class ProjectDAO {
    private static string $table = 'tbl_project';
    private static string $db = 'whim_db';

    private static array $schema = [
        'project_id' => 'int',
        'name' => 'string',
        'domain' => 'string',
        'active' => 'bool',
        'type' => 'string',
        'owner' => 'string',
        'ftp_host' => 'string',
        'ftp_user' => 'string',
        'ftp_pass' => 'string',
        'ssh_host' => 'string',
        'ssh_user' => 'string',
        'ssh_key_path' => 'string',
        'remote_web_root' => 'string',
        'local_dev_root' => 'string',
        'local_stage_root' => 'string',
        'hosting_provider' => 'string',
        'db_prefix' => 'string',
        'staging_db_name' => 'string',
        'remote_db_name' => 'string',
        'remote_db_user' => 'string',
        'remote_db_pass' => 'string',
        'local_db_name' => 'string',
        'local_db_user' => 'string',
        'local_db_pass' => 'string',
        'git_repo_url' => 'string',
        'last_deployed' => 'date',
        'backup_path' => 'string',
        'custom_scripts' => 'json',
        'deploy_exclude' => 'json'
    ];

    public static function getByName(string $name): ?Project {
        $data = SQLDataProvider::getInstance()->get('name', $name, self::$table, self::$db);
        return $data ? Project::fromArray($data) : null;
    }

    public static function getAll(): array {
        $rows = SQLDataProvider::getInstance()->getAll(self::$table, self::$db);
        return array_map(fn($row) => Project::fromArray($row), $rows);
    }
    
    public static function getFieldValue(Project $project, string $fieldName): mixed {
        $projectName = $project->getProjectName();
    
        if (!isset(self::$schema[$fieldName])) {
            throw new InvalidArgumentException("Unknown field: $fieldName");
        }
    
        foreach (self::getAll() as $p) {
            if ($p->getProjectName() === $projectName) {
                $raw = $p->get($fieldName);
                $type = self::$schema[$fieldName];
    
                return match ($type) {
                    'int'    => (int) $raw,
                    'bool'   => filter_var($raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
                    'string' => (string) $raw,
                    'date'   => is_string($raw) ? new DateTime($raw) : $raw,
                    'json'   => is_string($raw) ? json_decode($raw, true) : (array) $raw,
                    default  => $raw,
                };
            }
        }
    
        throw new RuntimeException("Project '{$projectName}' not found or field '{$fieldName}' unavailable.");
    }
    
    public static function getProjectName(Project $project): string {
        return self::getFieldValue($project, 'name');
    }
    
    public static function getProjectTypeName(Project $project): string {
        return self::getFieldValue($project, 'type');
    }
    
    public static function isActive(Project $project): bool {
        return self::getFieldValue($project, 'active');
    }
    
    public static function save(Project $project): void {
        $data = $project->toArray();
        self::validate($data);
        SQLDataProvider::getInstance()->save($data, self::$table, self::$db);
    }

    private static function validate(array $data): void {
        foreach ($data as $key => $value) {
            if (!array_key_exists($key, self::$schema)) {
                throw new \InvalidArgumentException("Unknown field: $key");
            }
            // TODO: Type checking if needed
        }
    }
}

