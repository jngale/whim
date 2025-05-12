<?php
declare(strict_types=1);
require_once __DIR__ . '/../bootstrap.php';
 
header('Content-Type: application/json');
ini_set('display_errors', '0');
error_reporting(0);

try {
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);

    if (!is_array($input)) {
        throw new Exception("Invalid JSON received");
    }

    $name = $input['name'] ?? throw new Exception("Missing project name");
    $typeName = $input['type'] ?? throw new Exception("Missing project type");

    if (in_array($name, ['whim', 'ajax', 'lib'])) {
        throw new RuntimeException("Cannot use reserved name for project: $name");
    }

    $projectPath = PROJECTS_ROOT . $name . '/';
    safeRemoveDir($projectPath);

    $type = $typeName::load($project);
    $creator = $type::projectCreator($name, $input);

    $creator->create();

    // ✅ Corrected this:
    $projectName = $creator->getProject()->getProjectName();


    gitInitRepo(
        $creator->getProject(),
        $creator->gitignoreTemplate()
    );

    echo json_encode(['success' => true]);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
    exit;
}

