<?php
declare(strict_types=1);
require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json');

$name = $_POST['name'] ?? '';
if (!$name) {
    echo json_encode(['success' => false, 'error' => 'Missing project name']);
    exit;
}

try { 
    $project = Project::getByName($name);

    foreach ($_POST as $key => $value) {
        if ($key === 'name') continue;
    
        // Translate HTML checkbox values to integer
        if ($key === 'active') {
            $value = $value === 'on' ? 1 : 0;
        }
    
        $project->set($key, $value);
    }
    

    $project->saveToDatabase();

    echo json_encode(['success' => true, 'record' => $project_getName]);

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
