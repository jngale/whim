<?php
declare(strict_types=1);

// 🟦 Define root
define('WHIM_ROOT', realpath(__DIR__) . '/');
define('ROOT_DIR', realpath(__DIR__) . '/');

// 🟦 Define paths
define('PROJECTS_ROOT', '/var/www/projects/');
define('STAGING_ROOT', '/var/www/staging/');  
define('BACKUP_ROOT', '/var/www/backup/');
define('HTML_ROOT', '/var/www/html/');
define('TEMPLATE_DIR', '/var/www/templates/');
define('SQL_DIR', '/var/whim_sql/');

define('LIB_DIR', WHIM_ROOT . 'lib/');
define('UI_DIR', WHIM_ROOT . 'ui/');
define('JS_DIR', WHIM_ROOT . 'js/');
define('AJAX_DIR', WHIM_ROOT . 'ajax/');
define('SCRIPTS_DIR', WHIM_ROOT . 'scripts/');

define('METACARD_DIR', UI_DIR . 'meta-cards/');
define('TYPES_DIR', LIB_DIR . 'proj-types/');
define('CREATORS_DIR', LIB_DIR . 'creators/');
define('TOOLS_DIR', LIB_DIR . 'tools/');
define('DAL_DIR', LIB_DIR . 'dal/');

define('REF_META_FILE', PROJECTS_ROOT . 'whim/ref/metadata_template.json');
define('SAFE_DIRS_FILE', PROJECTS_ROOT . 'safe_git_dirs.txt');


// 🟦 Define general constants
define('WHIM_OWNER', 'www-data');
define('WHIM_GROUP', 'devs');



// ✅ Load core classes
$loadFiles = [
    // 🟦 Utility & Core
    TOOLS_DIR . 'GenUtil.php',
    TOOLS_DIR . 'GitUtil.php',
    LIB_DIR . 'User.php',
    LIB_DIR . 'Project.php',

    // 🟦 DAL
    DAL_DIR . 'Connection.php',
    DAL_DIR . 'DatabaseManager.php',
    DAL_DIR . 'WhimDataInterface.php',
    DAL_DIR . 'DataProvider.php',
    DAL_DIR . 'SQLDataProvider.php',
    DAL_DIR . 'dao/UserDAO.php',
    DAL_DIR . 'dao/ProjectDAO.php',
    DAL_DIR . 'dao/UserProjectDAO.php',

    // 🟦 Project Types
    TYPES_DIR . 'ProjectType.php',
    TYPES_DIR . 'WordPressType.php',
    TYPES_DIR . 'LocalType.php',
    TYPES_DIR . 'PluginType.php',
    TYPES_DIR . 'WordPressFTPType.php',
    TYPES_DIR . 'WordPressSSHType.php',

    // 🟦 Creators
    CREATORS_DIR . 'ProjectCreatorInterface.php',
    CREATORS_DIR . 'ProjectCreator.php',
    CREATORS_DIR . 'WordPressProjectCreator.php',
    CREATORS_DIR . 'LocalProjectCreator.php',
    CREATORS_DIR . 'PluginProjectCreator.php',
    CREATORS_DIR . 'WordPressProjectCreator.php',

    // 🟦 Importers
    LIB_DIR . 'importers/Importer.php',
    LIB_DIR . 'importers/WordPressSSHImporter.php',

    // 🟦 UI
    UI_DIR . 'DisplayCard.php',
    UI_DIR . 'ProjectCard.php',
    UI_DIR . 'ProjectCreationPopup.php',
    UI_DIR . 'DeployCard.php',
    METACARD_DIR . 'MetadataAbstractCard.php',
    METACARD_DIR . 'ProjectSettingsCard.php',
    METACARD_DIR . 'DbCard.php',
    METACARD_DIR . 'SSHCard.php',
    METACARD_DIR . 'FTPCard.php',
    METACARD_DIR . 'NewProjectSettingsCard.php',
];


foreach ($loadFiles as $file) {
    require_once $file;
}

// ✅ Set whim db connection
$config = require WHIM_ROOT . 'config.php';
DatabaseManager::addConnection(Connection::load($config, 'whim_db'), 'whim_db');

// ✅ Set global active user
global $activeUser;
$activeUser = User::load();
