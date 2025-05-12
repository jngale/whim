<?php
declare(strict_types=1);
if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', realpath(__DIR__ . '/../') . '/');
    require_once ROOT_DIR . 'bootstrap.php';
}

// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['project'])) {
//     $name = $_POST['project'];
//     $action = $_POST['action'];
// 
//     try {
//         $project = Project::getByName($name);  // DAO-backed internally
//         $project->performAction($action);      // This now calls the appropriate type method
// 
//         echo "✅ Action '$action' completed for project '$name'";
//     } catch (Throwable $e) {
//         http_response_code(500);
//         echo "❌ " . $e->getMessage();
//     }
// 
//     exit;
// }

// Load and prepare project cards
$cards = [];
$projects = Project::getActive();
foreach ($projects as $project) {
    $cards[] = ProjectCard::load($project);
}

// Load project creation card (for modal)
$createCard = ProjectCreationPopup::load([]);
?>

<div class="wrap">
    <div class="header-with-button">
        <h1>Projects</h1>
        <button class="button" onclick="openModal()">+ Create New Project</button>
    </div>
    <div class="card-container">
        <?php foreach ($cards as $card): ?>
            <?= $card->render() ?>
        <?php endforeach; ?>
    </div>

    <!-- Modal -->
    <div class="modal-overlay" id="projectModal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <?= $createCard->render() ?>
        </div>
    </div>

    <div id="import-anim" class="hidden">
        <div class="import-animation">
            <div class="cloud">Bluehost ☁️</div>
            <div class="path">
                <div class="file">📄</div>
            </div>
            <div class="server">WHIM 🖥️</div>
        </div>
    </div>

</div>



<script src="js/projects.js"></script>
