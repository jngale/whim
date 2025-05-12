<?php
declare(strict_types=1);

$projects_root = PROJECTS_ROOT;
$ref_path = REF_META_FILE;
$ref_meta = file_exists($ref_path) ? json_decode(file_get_contents($ref_path), true) : [];

$projects = Project::getAll($projects_root);
$selected = $_GET['project'] ?? null;

// Project dropdown
echo '<form method="get" action="" style="text-align:center; margin-bottom: 2rem;">';
echo '<input type="hidden" name="tab" value="metadata">';
echo '<label for="project-select" style="font-weight: bold;">Select project:</label>';
echo '<select name="project" id="project-select" onchange="this.form.submit()" style="margin-left: 10px;">';
echo '<option disabled ' . (!$selected ? 'selected' : '') . '>Select a project</option>';
foreach ($projects as $project) {
    $selectedAttr = ($project->getProjectName() === $selected) ? 'selected' : '';
    echo "<option value=\"{$project->getProjectName()}\" $selectedAttr>{$project->getProjectName()}</option>";
}
echo '</select>';
echo '</form>';

// Stop here if no project selected
if (!$selected) {
    return;
}

$project = Project::getByName($selected);

// Metadata form
echo '<form id="meta-form" method="post" class="metadata-panel">';
echo '<input type="hidden" name="name" value="' . htmlspecialchars($project->getProjectName()) . '">';
echo "<h2>Metadata for {$project->getProjectName()}</h2>";
echo '<button id="save-meta-btn" type="button" class="btn btn-primary">Save Metadata</button>';
echo '<div id="save-status" style="margin-top: 10px;"></div>';

$cardClasses = [
    'ProjectSettingsCard',
    'DbCard',
    'SSHCard',
    'FTPCard',
    'GitCard',
    'OtherInfoCard',
];

foreach ($cardClasses as $class) {
    $file = UI_DIR . "meta-cards/{$class}.php";
    if (!file_exists($file)) {
        error_log("[JG metadata.php] Missing file for class: $class");
        echo "<!-- Missing file for {$class} -->";
        continue;
    }

    require_once $file;

    if (!class_exists($class)) {
        error_log("[JG metadata.php] Missing class: $class");
        echo "<!-- Missing class {$class} -->";
        continue;
    }

    $card = $class::load($project, $ref_meta);
    $shouldDisp = $card->shouldDisplay();
    error_log("[JG metadata.php] Should display $class: $shouldDisp");
    if ($card->shouldDisplay()) {
        echo $card->render();
    }
}

echo '</form>';

echo '<script src="js/metadata.js"></script>';


