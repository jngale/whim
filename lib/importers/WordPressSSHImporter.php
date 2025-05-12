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
            $output[] = "[Import] Running fixPermissions()...";
            $this->project->getProjectType()->fixPermissions($this->project);
            $output[] = "[Import] ✅ Permissions fixed.";
        } else {
            $output[] = "[Import] ⚠️ Skipped permission fix (directory missing)";
        }
    
        $output[] = "[Import] ✅ Site import completed successfully.";
    }
    
    public function importDatabase(): void {
        $project = $this->project;
        $meta = $project->getMetadata();
        $projectName = $project->getProjectName();
        $devPath = SQL_DIR . $projectName . ".dev.sql";
        $dumpFile = SQL_DIR . $projectName . ".remote.sql";

        $provider   = escapeshellarg($meta['hosting_provider']);
        $sshHost    = escapeshellarg($meta['ssh_host']);
        $sshUser    = escapeshellarg($meta['ssh_user']);
        $sshKey     = escapeshellarg($meta['ssh_key_path']);
        $remoteDb   = escapeshellarg($meta['remote_db_name']);
        $remoteUser = escapeshellarg($meta['remote_db_user']);
        $remotePass = escapeshellarg($meta['remote_db_pass']);
        $localDb   = $meta['local_db_name'];
        $localUser = $meta['local_db_user'];
        $localPass = $meta['local_db_pass'];
    
        error_log("[DEBUG] SSH Importer Metadata: " . json_encode($meta, JSON_PRETTY_PRINT));
        error_log("[Import] Starting database import for '$projectName'");
    
        $requiredFields = [
            'ssh_host', 'ssh_user', 'ssh_key_path',
            'remote_db_name', 'remote_db_user', 'remote_db_pass',
            'local_db_name', 'local_db_user', 'local_db_pass'
        ];
    
        foreach ($requiredFields as $field) {
            if (empty($meta[$field])) {
                error_log("[Import] ❌ Missing required metadata field: '$field'");
                return;
            }
        }
    
        $cmd = "ssh -o IdentitiesOnly=yes -i $sshKey $sshUser@$sshHost 'mysqldump -u$remoteUser -p$remotePass $remoteDb' | cat > $dumpFile";
    
        error_log("[DEBUG] SSH dump command: $cmd");
    
        shell_exec($cmd);
    
        if (!file_exists($dumpFile) || filesize($dumpFile) < 100) {
            error_log("[Import] ❌ Dump failed or empty file at $dumpFile");
            return;
        } else {
            error_log("[Import] ✅ Dump written to $dumpFile");
        }

        // ✅ Ensure the user exists locally with correct privileges
        $grantSql = sprintf(
            "CREATE USER IF NOT EXISTS '%s'@'localhost' IDENTIFIED BY '%s';" .
            "GRANT ALL PRIVILEGES ON `%s`.* TO '%s'@'localhost';" .
            "FLUSH PRIVILEGES;",
            $localUser,
            $localPass,
            $localDb,
            $localUser
        );

        $mysqli = new \mysqli('localhost', 'root', 'Ripple'); // or another admin user if preferred
        if ($mysqli->connect_error) {
            error_log("[Import] ❌ MySQL root connection failed: " . $mysqli->connect_error);
            return;
        }

        if (!$mysqli->multi_query($grantSql)) {
            error_log("[Import] ❌ Failed to create user or grant privileges: " . $mysqli->error);
            $mysqli->close();
            return;
        }

        do {
            // flush remaining results if any
            if ($res = $mysqli->store_result()) {
                $res->free();
            }
        } while ($mysqli->more_results() && $mysqli->next_result());

        $mysqli->close();
        error_log("[Import] ✅ MySQL user '$localUser' created/granted for '$localDb'");

    
        // ➕ Drop and recreate the local database

        $mysqli = new \mysqli('localhost', $localUser, $localPass);
        if ($mysqli->connect_error) {
            error_log("[Import] ❌ MySQL connect failed: " . $mysqli->connect_error);
            return;
        }
    
        if (!$mysqli->query("DROP DATABASE IF EXISTS `$localDb`")) {
            error_log("[Import] ❌ Failed to drop database '$localDb': " . $mysqli->error);
            $mysqli->close();
            return;
        }
    
        if (!$mysqli->query("CREATE DATABASE `$localDb`")) {
            error_log("[Import] ❌ Failed to create database '$localDb': " . $mysqli->error);
            $mysqli->close();
            return;
        }
    
        error_log("[Import] ✅ Successfully dropped and recreated database '$localDb'");
        $mysqli->close();


        // ➕ Now import the SQL file into the new database
        $importCmd = "mysql -u$localUser -p$localPass $localDb < $dumpFile";
        error_log("[DEBUG] Local import command: $importCmd");
    
        shell_exec($importCmd);
        copy($dumpFile, $devPath);

        // Git stuff
        // gitSwitchOrCreateBranchIfMissing($project, $projectName, SQL_DIR);
        // gitCommit($project, "Imported $projectName from $provider", SQL_DIR);
    
        error_log("[Import] ✅ Import complete for database '$localDb'");

        // 🔧 Patch home and siteurl to local dev URL
        $domain = $project->get('domain');
        $localUrl = "https://{$domain}.dev.local";
        $wpPath = $project->getPath();

        $updateCmd = "wp option update home '$localUrl' --path='$wpPath' && " .
                    "wp option update siteurl '$localUrl' --path='$wpPath'";

        try {
            shell_exec($updateCmd);
            error_log("[Import] ✅ Updated WP URLs to $localUrl");
        } catch (\Throwable $e) {
            error_log("[Import] ⚠️ Failed to update WP URLs: " . $e->getMessage());
        }
    }        
}    