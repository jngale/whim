<?php
declare(strict_types=1);

class UserProjectDAO {
    private static string $table = 'tbl_user_project';
    private static string $db = 'whim_db';

    private static array $schema = [
        'user_project_id' => 'int',
        'user_id' => 'int',
        'project_id' => 'int',
    ];

    public static function getProjectsForUser(int $userId): array {
        $conn = DatabaseManager::getConnection(self::$db);

        $sql = "
            SELECT p.* FROM tbl_project p
            INNER JOIN tbl_user_project up ON up.project_id = p.project_id
            WHERE up.user_id = ?
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        $projects = [];

        while ($row = $result->fetch_assoc()) {
            $projects[] = Project::fromArray($row);
        }

        return $projects;
    }

    public static function linkUserToProject(int $userId, int $projectId): void {
        $data = [
            'user_id' => $userId,
            'project_id' => $projectId
        ];
        SQLDataProvider::getInstance()->save($data, self::$table, self::$db);
    }
}

