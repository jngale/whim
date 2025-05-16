<?php
declare(strict_types=1);
require_once __DIR__ . '/../bootstrap.php';

$projectName = $_GET['project'] ?? '';
if (!$projectName) {
    http_response_code(400);
    exit("Missing project name.");
}

try {
    $project = Project::getByName($projectName);
    $project->getProjectType->fixPermissions();
    echo "✅ Permissions fixed for $projectName";
} catch (Throwable $e) {
    http_response_code(500);
    echo "❌ Error: " . $e->getMessage();
}
