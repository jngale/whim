<?php
declare(strict_types=1);

class Connection {
    private \mysqli $conn;
    private string $name;

    public static function load(array $config, string $name = 'default'): Connection {
        $obj = new self();
        $obj->name = $name;

        $host = $config['db_host'] ?? 'localhost';
        $user = $config['db_user'] ?? '';
        $pass = $config['db_pass'] ?? '';
        $db   = $config['db_name'] ?? '';

        $obj->conn = new \mysqli($host, $user, $pass, $db);

        if ($obj->conn->connect_error) {
            throw new \RuntimeException("❌ Failed to connect to [$name]: " . $obj->conn->connect_error);
        }

        $obj->conn->set_charset('utf8mb4');

        return $obj;
    }

    public function query(string $sql): \mysqli_result|bool {
        $result = $this->conn->query($sql);
        if ($this->conn->error) {
            throw new \RuntimeException("❌ Query error on [{$this->name}]: {$this->conn->error}\nSQL: $sql");
        }
        return $result;
    }

    public function prepare(string $sql): \mysqli_stmt {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new \RuntimeException("❌ Prepare failed on [{$this->name}]: {$this->conn->error}\nSQL: $sql");
        }
        return $stmt;
    }

    public function close(): void {
        $this->conn->close();
    }

    public function getName(): string {
        return $this->name;
    }

    public function getRaw(): \mysqli {
        return $this->conn;
    }
}
