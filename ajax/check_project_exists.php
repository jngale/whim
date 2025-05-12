<?php
declare(strict_types=1);
require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json');
ini_set('display_errors', '0');
error_reporting(0);

try {
    if (empty($_GET['name'])) {
        throw new Exception("Missing project name");
    }

    $name = basename(trim($_GET['name'])); // Extra precaution
    $projectPath = PROJECTS_ROOT . $name . '/';

    $exists = is_dir($projectPath);

    echo json_encode([
        'exists' => $exists
    ]);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'exists' => false,
        'error' => $e->getMessage()
    ]);
    exit;
}
