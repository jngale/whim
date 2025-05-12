<?php
declare(strict_types=1);

class UserDAO {
    private static string $table = 'tbl_user';
    private static string $db = 'whim_db';

    private static array $schema = [
        'user_id' => 'int',
        'name' => 'string',
        'email' => 'string',
        'password' => 'string',
        'is_admin' => 'bool',
        'has_database_access' => 'bool'
    ];

    public static function getByName(string $name): ?User {
        $data = SQLDataProvider::getInstance()->get('name', $name, self::$table, self::$db);
        return $data ? User::fromArray($data) : null;
    }

    public static function getAll(): array {
        $rows = SQLDataProvider::getInstance()->getAll(self::$table, self::$db);
        return array_map(fn($row) => User::fromArray($row), $rows);
    }

    public static function getFieldValue(User $user, string $fieldName): mixed {

        $dataProvider = SQLDataProvider::getInstance();
        $projectName = $project->getProjectName();
        
        return $dataProvider->getColumnValue('whim_db', 'tbl_user', 'name', $user->getName, $fieldName);
    }

    public static function save(User $user): void {
        $data = $user->toArray();
        self::validate($data);
        SQLDataProvider::getInstance()->save($data, self::$table, self::$db);
    }

    private static function validate(array $data): void {
        foreach ($data as $key => $value) {
            if (!array_key_exists($key, self::$schema)) {
                throw new \InvalidArgumentException("Unknown field: $key");
            }
            // Optional: Add type enforcement later
        }
    }
}

