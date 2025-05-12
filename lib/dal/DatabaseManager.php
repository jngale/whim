<?php
declare(strict_types=1);

class DatabaseManager {
    /** @var array<string, Connection> */
    private static array $connections = [];

    
    // Loads a database connection using config array
    public static function addConnection(Connection $conn, string $name = 'default'): void {
        if (isset(self::$connections[$name])) {
            return;
        }
    
        self::$connections[$name] = $conn;
        error_log("✅ whim_db connection added");

    }
    

     // Returns a named Connection instance
    public static function getConnection(string $name = 'default'): Connection {
        if (!isset(self::$connections[$name])) {
            error_log("❌ Available connections: " . implode(', ', array_keys(self::$connections)));

            throw new \RuntimeException("❌ No active connection named '$name'");
        }
        return self::$connections[$name];
    }

    // Executes a query using the named connection
    public static function query(string $sql, string $name = 'default'): \mysqli_result|bool {
        return self::getConnection($name)->query($sql);
    }

    // Prepares a statement using the named connection
    public static function prepare(string $sql, string $name = 'default'): \mysqli_stmt {
        return self::getConnection($name)->prepare($sql);
    }

    // Closes a specific connection
    public static function close(string $name = 'default'): void {
        if (isset(self::$connections[$name])) {
            self::$connections[$name]->close();
            unset(self::$connections[$name]);
        }
    }

    // Closes all active connections
    public static function closeAll(): void {
        foreach (array_keys(self::$connections) as $name) {
            self::close($name);
        }
    }
}
