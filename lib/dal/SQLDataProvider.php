<?php
declare(strict_types=1);

class SQLDataProvider extends DataProvider {
    private static ?self $instance = null;

    public static function getInstance(): self {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get(string $field, mixed $value, string $table, string $db): ?array {
        $conn = DatabaseManager::getConnection($db);
        $sql = "SELECT * FROM `$table` WHERE `$field` = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new \RuntimeException("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param($this->getBindType($value), $value);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : null;
    }

    public function getAll(string $table, string $db): array {
        $conn = DatabaseManager::getConnection($db);
        $sql = "SELECT * FROM `$table`";
        $result = $conn->query($sql);
        if (!$result) {
            throw new \RuntimeException("Query failed: " . $conn->error);
        }

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function getProjectTypeName(Project $project, string $table, string $db): string {
        $projectName = $project->getProjectName();
        $sql = "SELECT type FROM `$table` WHERE name = ? LIMIT 1";
        $result = $this->querySingle($sql, [$projectName], $db);
    
        if (!$result || !isset($result['type'])) {
            throw new \RuntimeException("Project type not found for name: $name");
        }
    
        return $result['type'];
    }

    public function getColumnValue(
        string $db,
        string $table,
        string $lookupField,
        mixed $lookupValue,
        string $returnColumnName
    ): mixed {
        
        $conn = DatabaseManager::getConnection($db);
        $sql = "SELECT `$returnColumnName` FROM `$table` WHERE `$lookupField` = ? LIMIT 1";
    
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new \RuntimeException("Prepare failed: " . $conn->error);
        }
    
        $stmt->bind_param($this->getBindType($lookupValue), $lookupValue);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if (!$result) {
            throw new \RuntimeException("Query failed: " . $conn->error);
        }
    
        $row = $result->fetch_assoc();
        return $row[$returnColumnName] ?? null;
    }
    

    public function querySingle(string $sql, array $params, string $db): ?array {
    $conn = DatabaseManager::getConnection($db);
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new \RuntimeException("Prepare failed: " . $conn->error);
    }

    if (!empty($params)) {
        $types = implode('', array_map([$this, 'getBindType'], $params));
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    return $result ? $result->fetch_assoc() : null;
}


    public function save(array $data, string $table, string $db): void {
        $conn = DatabaseManager::getConnection($db);
        $fields = array_keys($data);
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));
        $updates = implode(', ', array_map(fn($f) => "`$f` = VALUES(`$f`)", $fields));

        $sql = "INSERT INTO `$table` (`" . implode('`,`', $fields) . "`) VALUES ($placeholders)
                ON DUPLICATE KEY UPDATE $updates";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new \RuntimeException("Prepare failed: " . $conn->error);
        }

        $types = '';
        $values = [];
        foreach ($data as $val) {
            $types .= $this->getBindType($val);
            $values[] = $val;
        }

        $stmt->bind_param($types, ...$values);
        $stmt->execute();
    }

    private function getBindType(mixed $val): string {
        return match (gettype($val)) {
            'integer' => 'i',
            'double'  => 'd',
            'string'  => 's',
            'boolean' => 'i',
            default   => 's'
        };
    }
}
