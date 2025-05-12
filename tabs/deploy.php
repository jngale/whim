<?php
declare(strict_types=1);

$projects = Project::getActive(PROJECTS_ROOT);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['project']) && !empty($_POST['action'])) {
    $dir = $_POST['project'];
    $path = PROJECTS_ROOT . $dir;
    $project = Project::getByName($_POST['name']);

    echo "<pre>";
    switch ($_POST['action']) {
        case 'deploy':
            $project->deploy();
            break;
        case 'backup':
            $project->backup();
            break;
        case 'restore':
            $project->restore();
            break;
        default:
            echo "Unknown action.";
    }
    echo "</pre>";
}

// Load and prepare project cards
$cards = [];
$projects = Project::getActive();
foreach ($projects as $project) {
    $cards[] = DeployCard::load($project);
}
?>

<?php
// Skip rendering cards if this is an AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exit;
}
?>

<div class="wrap">
    <h2>Deploy Projects</h2>
    <div class="project-list">
        <?php foreach ($projects as $project): ?>
            <?php
            try {
                echo $project->getProjectType()->getDeployCard();
            } catch (Throwable $e) {
                echo "<div class='card error'>";
                echo "<strong>Error loading project '{$project->name}'</strong><br>";
                echo htmlspecialchars($e->getMessage());
                echo "</div>";
            }
            ?>
        <?php endforeach; ?>
    </div>
</div>
