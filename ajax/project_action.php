<?php
declare(strict_types=1);

require_once '../bootstrap.php';
require_once LIB_DIR . 'Project.php';

header('Content-Type: text/plain');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'], $_POST['project'])) {
    http_response_code(400);
    echo "❌ Missing required parameters";
    exit;
}

$name = $_POST['project'];
$action = $_POST['action'];

try {
    $project = Project::getByName($name);
    $project->performAction($action);
    echo "✅ Action '$action' completed for project '$name'";
} catch (Throwable $e) {
    http_response_code(500);
    echo "❌ " . $e->getMessage();
}
