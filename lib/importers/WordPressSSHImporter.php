<?php
declare(strict_types=1);

class WordPressSSHImporter extends Importer {

    public function importSite(): void {
        $output = [];
        $project = $this->project;
        $meta = $project->getMetadata();
        $projectName = $project->getProjectName();

        error_log("[DEBUG] SSH Importer Metadata: " . json_encode($meta, JSON_PRETTY_PRINT));

    
        error_log("[DEBUG] WordPressSSHImporter->importSite -- Project $projectName");
        $output[] = "[Import] Starting site import for '$projectName'";
    
        // Check required metadata fields
        $requiredFields = ['ssh_host', 'ssh_user', 'ssh_key_path', 'remote_web_root', 'local_dev_root'];
        foreach ($requiredFields as $field) {
            if (empty($meta[$field])) {
                error_log("[DEBUG] Missing field: $field");
                
            }
        }
    
        $remoteHost = $project->get('ssh_host');
        $remoteUser = $project->get('ssh_user');
        $sshKey     = $project->get('ssh_key_path');
        $remoteRoot = rtrim($project->get('remote_web_root'), '/') . '/';
        $localRoot  = rtrim($project->get('local_dev_root'), '/') . '/';
    
        $output[] = "[Import] Remote: $remoteUser@$remoteHost:$remoteRoot";
        $output[] = "[Import] Local directory: $localRoot";
        $output[] = "[Import] SSH key: $sshKey";
    
        // Prepare local directory
        try {
            assertNotInsideWhim($localRoot);
            if (!is_dir($localRoot)) {
                $output[] = "[Import] Directory does not exist, creating...";
                safeCreateDirectory($localRoot);
                $output[] = "[Import] ✅ Created local root: $localRoot";
            } else {
                $output[] = "[Import] Directory exists, replacing contents...";
                removeDirectory($localRoot);
                safeCreateDirectory($localRoot);
                $output[] = "[Import] ✅ Reinitialized local root: $localRoot";
            }
        } catch (Exception $e) {
            $output[] = "[Import] ❌ Directory setup failed: " . $e->getMessage();
        }
    
        // Execute rsync
        $cmd = sprintf(
            'rsync -avz --progress -e "ssh -i %s" %s@%s:%s %s',
            escapeshellarg($sshKey),
            escapeshellarg($remoteUser),
            escapeshellarg($remoteHost),
            escapeshellarg($remoteRoot),
            escapeshellarg($localRoot)
        );
    
        $output[] = "[Import] Running rsync...";
        try {
            $output[] = execCmd($cmd);
            $output[] = "[Import] ✅ rsync complete.";
        } catch (RuntimeException $e) {
            $output[] = "[Import] ❌ rsync failed: " . $e->getMessage();
        }
    
        // Permissions
        if (is_dir($localRoot)) {
            $output[] = "[Import] Running configureProject()...";
            $this->$project->configureProject($this->project);
            $output[] = "[Import] ✅ Permissions fixed.";
        } else {
            $output[] = "[Import] ⚠️ Skipped project configuration (directory missing)";
        }
    
        $output[] = "[Import] ✅ Site import completed successfully.";
    }
    
    public function importDatabase(): void {
        $project = $this->project;
        $meta = $project->getMetadata();
    
        error_log("[DEBUG] SSH Importer Metadata: " . json_encode($meta, JSON_PRETTY_PRINT));
        error_log("[Import] Starting database import for '{$project->getProjectName()}'");
    
        if (!$this->validateMetadata($meta)) return;
        if (!$this->downloadRemoteDump($meta)) return;
        if (!$this->createOrGrantLocalUser($meta)) return;
        if (!$this->resetLocalDatabase($meta)) return;
        if (!$this->importDumpFile($meta)) return;
    
        $this->finalizeImport($project, $meta);
    }
    
    
    // ─────────────────────────────────────────────
    // 📦 Group: Validation
    // ─────────────────────────────────────────────
    
    private function validateMetadata(array $meta): bool {
        $required = [
            'ssh_host', 'ssh_user', 'ssh_key_path',
            'remote_db_name', 'remote_db_user', 'remote_db_pass',
            'local_db_name', 'local_db_user', 'local_db_pass'
        ];
    
        foreach ($required as $field) {
            if (empty($meta[$field])) {
                error_log("[Import] ❌ Missing required metadata field: '$field'");
                return false;
            }
        }
    
        return true;
    }
    
    
    // ─────────────────────────────────────────────
    // 📦 Group: SSH Dump
    // ─────────────────────────────────────────────
    
    private function downloadRemoteDump(array $meta): bool {
        $projectName = $this->project->getProjectName();
        $dumpFile = SQL_DIR . $projectName . ".remote.sql";
    
        $cmd = sprintf(
            "ssh -o IdentitiesOnly=yes -i %s %s@%s 'mysqldump -u%s -p%s %s' > %s",
            escapeshellarg($meta['ssh_key_path']),
            escapeshellarg($meta['ssh_user']),
            escapeshellarg($meta['ssh_host']),
            escapeshellarg($meta['remote_db_user']),
            escapeshellarg($meta['remote_db_pass']),
            escapeshellarg($meta['remote_db_name']),
            escapeshellarg($dumpFile)
        );
    
        error_log("[DEBUG] SSH dump command: $cmd");
        shell_exec($cmd);
    
        if (!file_exists($dumpFile) || filesize($dumpFile) < 100) {
            error_log("[Import] ❌ Dump failed or empty file at $dumpFile");
            return false;
        }
    
        error_log("[Import] ✅ Dump written to $dumpFile");
        return true;
    }
    
    
    // ─────────────────────────────────────────────
    // 📦 Group: MySQL User Setup
    // ─────────────────────────────────────────────
    
    private function createOrGrantLocalUser(array $meta): bool {
        $sql = sprintf(
            "CREATE USER IF NOT EXISTS '%s'@'localhost' IDENTIFIED BY '%s';" .
            "GRANT ALL PRIVILEGES ON `%s`.* TO '%s'@'localhost';" .
            "FLUSH PRIVILEGES;",
            $meta['local_db_user'], $meta['local_db_pass'],
            $meta['local_db_name'], $meta['local_db_user']
        );
    
        $mysqli = new \mysqli('localhost', 'root', 'Ripple');
        if ($mysqli->connect_error) {
            error_log("[Import] ❌ MySQL root connection failed: " . $mysqli->connect_error);
            return false;
        }
    
        if (!$mysqli->multi_query($sql)) {
            error_log("[Import] ❌ Failed to create user or grant privileges: " . $mysqli->error);
            $mysqli->close();
            return false;
        }
    
        do {
            if ($res = $mysqli->store_result()) $res->free();
        } while ($mysqli->more_results() && $mysqli->next_result());
    
        $mysqli->close();
        error_log("[Import] ✅ MySQL user created/granted");
        return true;
    }
    
    
    // ─────────────────────────────────────────────
    // 📦 Group: Database Drop/Create
    // ─────────────────────────────────────────────
    
    private function resetLocalDatabase(array $meta): bool {
        $mysqli = new \mysqli('localhost', $meta['local_db_user'], $meta['local_db_pass']);
        if ($mysqli->connect_error) {
            error_log("[Import] ❌ MySQL connect failed: " . $mysqli->connect_error);
            return false;
        }
    
        $localDb = $meta['local_db_name'];
    
        if (!$mysqli->query("DROP DATABASE IF EXISTS `$localDb`")) {
            error_log("[Import] ❌ Failed to drop database: " . $mysqli->error);
            $mysqli->close();
            return false;
        }
    
        if (!$mysqli->query("CREATE DATABASE `$localDb`")) {
            error_log("[Import] ❌ Failed to create database: " . $mysqli->error);
            $mysqli->close();
            return false;
        }
    
        $mysqli->close();
        error_log("[Import] ✅ Reset database '$localDb'");
        return true;
    }
    
    
    // ─────────────────────────────────────────────
    // 📦 Group: Import SQL Dump
    // ─────────────────────────────────────────────
    
    private function importDumpFile(array $meta): bool {
        $projectName = $this->project->getProjectName();
        $dumpFile = SQL_DIR . $projectName . ".remote.sql";
        $devPath = SQL_DIR . $projectName . ".dev.sql";
    
        $cmd = sprintf(
            "mysql -u%s -p%s %s < %s",
            escapeshellarg($meta['local_db_user']),
            escapeshellarg($meta['local_db_pass']),
            escapeshellarg($meta['local_db_name']),
            escapeshellarg($dumpFile)
        );
    
        error_log("[DEBUG] Importing SQL file: $cmd");
        shell_exec($cmd);
        copy($dumpFile, $devPath);
        return true;
    }
    
    
    // ─────────────────────────────────────────────
    // 📦 Group: Final Steps (URLs + Git)
    // ─────────────────────────────────────────────
    
    private function finalizeImport(Project $project, array $meta): void {
        $projectName = $project->getProjectName();
        $provider = $meta['hosting_provider'];
        $domain = $meta['domain'];
        $wpPath = $project->getPath();
        $localUrl = "http://{$domain}.dev.local"; // ✅ Use http to avoid SSL hassle
    
        $cmd = "wp option update home '$localUrl' --path='$wpPath' && " .
               "wp option update siteurl '$localUrl' --path='$wpPath'";
        shell_exec($cmd);
    
        // gitSwitchOrCreateBranchIfMissing($project, $projectName, SQL_DIR);
        // gitCommit($project, "Imported $projectName from $provider", SQL_DIR);
    
        error_log("[Import] ✅ WP URLs updated to $localUrl");
    }    
}    