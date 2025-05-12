<?php
declare(strict_types=1);
require_once __DIR__ . '/../bootstrap.php';


ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Confirm we're here
error_log("[open_vscode.php] ⚡ Script called.");

// Read and validate input
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['name'])) {
    error_log("[open_vscode.php] 🚫 Missing project name");
    http_response_code(400);
    echo "Missing project name";
    exit;
}

// $project = basename($data['name']);
// $path = PROJECTS_ROOT . $project;

$projectName = basename($data['name']);
error_log("[open_vscode.php] 📁 Requested project: $projectName ");

$project = Project::getByName($projectName);
$path = $project->getVSCodePath();


// Validate project path
if (!is_dir($path)) {
    error_log("[open_vscode.php] 🚫 Project directory not found: $path");
    http_response_code(404);
    echo "Project directory not found";
    exit;
}

// Prepare command
$escapedPath = escapeshellarg($path);
$user = 'john';  // still ok

$cmd = "sudo -u $user DISPLAY=:1 /usr/bin/code $escapedPath > /dev/null 2>&1 &";

// Run command
try {
    execCmd($cmd);
    error_log("[open_vscode.php] ✅ VS Code launch command executed.");
    echo "VS Code launch requested successfully.";
} catch (RuntimeException $e) {
    error_log("[open_vscode.php] ❌ Command execution failed: " . $e->getMessage());
    http_response_code(500);
    echo "Failed to open VS Code: " . $e->getMessage();
}
