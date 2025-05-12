<?php
declare(strict_types=1);
if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', realpath(__DIR__) . '/');
}
require_once __DIR__ . '/bootstrap.php'; 

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

error_log(">>> ENTERING WHIM index.php");




$tab = $_GET['tab'] ?? 'projects';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>WHIM</title>
    <link rel="stylesheet" href="css/whim.css">
    <script src="js/whim.js?v=<?= time() ?>"></script>
    <script src="js/open_vscode.js"></script>

</head>
<body>
    <?php global $activeUser; ?>
    <div class="active-user-bar">
        👤 Active User: <span class="active-user-name"><?= htmlspecialchars($activeUser->name) ?></span> (<?= htmlspecialchars($activeUser->email) ?>)

        <div class="user-switcher">
            <label for="user-select">Switch User:</label>
            <select id="user-select">
                <option value="john" <?= $activeUser->userName === 'john' ? 'selected' : '' ?>>John</option>
                <option value="chuck" <?= $activeUser->userName === 'chuck' ? 'selected' : '' ?>>Chuck</option>
            </select>
        </div>
    </div>


    <div class="tabs">
        <button class="tablink <?= $tab === 'projects' ? 'active' : '' ?>" data-tab="projects">Projects</button>
        <button class="tablink <?= $tab === 'metadata' ? 'active' : '' ?>" data-tab="metadata">Metadata</button>
        <button class="tablink <?= $tab === 'users' ? 'active' : '' ?>" data-tab="users">Users</button>
    </div>

    <div class="tabcontent" id="projects" style="<?= $tab === 'projects' ? '' : 'display:none' ?>">
        <?php include "tabs/projects.php"; ?>
    </div>
    <div class="tabcontent" id="metadata" style="<?= $tab === 'metadata' ? '' : 'display:none' ?>">
        <?php include "tabs/metadata.php"; ?>
    </div>
    <div class="tabcontent" id="users" style="<?= $tab === 'users' ? '' : 'display:none' ?>">
        <?php include "tabs/users.php"; ?>
    </div>
    <div id="toast-container"></div>

    <div id="status-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="status-modal-title">Status
                <span id="status-modal-spinner" class="button-spinner" style="margin-left: 10px;"></span>
                </h2>
            </div>

            <!-- 🎬 Animation inserted here -->
            <div id="import-animation" class="import-animation hidden">
                <div class="cloud">Bluehost</div>
                <div class="path">
                    <div class="file">📦</div>
                </div>
                <div class="cloud">WHIM</div>
            </div>


            <div class="modal-body-scrollable">
                <pre id="status-modal-log" style="white-space: pre-wrap;"></pre>
            </div>
            <div class="modal-footer">
                <button class="button" onclick="StatusModal.hide()">Close</button>
            </div>
        </div>
    </div>



</body>
</html>
