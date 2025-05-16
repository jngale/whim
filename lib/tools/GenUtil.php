<?php
declare(strict_types=1);

    function execCmd(string $cmd, string $cwd = null): string {
        $prefix = $cwd ? "cd $cwd && " : "";
        $full = $prefix . $cmd . " 2>&1";

        error_log("📦 execCmd: $full");

        $outputArr = [];
        exec($full, $outputArr, $code);
        $outputStr = implode("\n", $outputArr);

        error_log("📤 Output:\n" . $outputStr);
        error_log("💥 Exit code: $code");

        if ($code !== 0) {
            throw new RuntimeException("Command failed:\n$full\n" . $outputStr);
        }

        return $outputStr;
    }


function assertNotInsideWhim(string $path): void {
    $normalizedPath = rtrim($path, '/');
    $whimPath = realpath(WHIM_ROOT);

    if ($whimPath === false) {
        return;
    }

    // Fall back to simple string comparison if path doesn't exist
    if (is_dir($normalizedPath)) {
        $resolved = realpath($normalizedPath);
        if ($resolved && strpos($resolved, $whimPath) === 0) {
            error_log("WHIM SAFETY] 🚫 Attempted to write into WHIM (existing path): $path");
            throw new RuntimeException("Refusing to write inside WHIM directory tree.");
        }
    } else {
        // Use raw comparison
        if (strpos($normalizedPath, $whimPath) === 0) {
            error_log("[WHIM SAFETY] 🚫 Attempted to write into WHIM (planned path): $path");
            throw new RuntimeException("Refusing to write inside WHIM directory tree.");
        }
    }
}



function safeRemoveDir(string $path): void {
    if (!file_exists($path)) {
        error_log("[WHIM GenUtil::safeRemoveDir] ⚠️ Skipped: path does not exist: $path");
        return;
    }

    $real = realpath($path);
    if ($real === false) {
        error_log("[WHIM GenUtil::safeRemoveDir] ⚠️ Skipped: realpath failed for: $path");
        return;
    }

    // ✅ PROTECT: refuse to delete if folder contains unexpected files
    $allowedFiles = ['.', '..', '.gitignore'];
    $contents = scandir($real);

    foreach ($contents as $item) {
        if (!in_array($item, $allowedFiles, true)) {
            error_log("[WHIM GenUtil::safeRemoveDir] 🚫 REFUSED to delete non-empty directory: $real (found $item)");
            return;
        }
    }

    // ✅ Now safe to remove
    execCmd("rm -rf " . escapeshellarg($real));
}

function removeDirectory(string $path): void {
    if (!file_exists($path)) {
        error_log("[WHIM GenUtil::safeRemoveDir] ⚠️ Skipped: path does not exist: $path");
        return;
    }

    $real = realpath($path);
    if ($real === false) {
        error_log("[WHIM GenUtil::safeRemoveDir] ⚠️ Skipped: realpath failed for: $path");
        return;
    }

    execCmd("rm -rf " . escapeshellarg($real));
}


function createDirectory(): void {
    if (!mkdir($this->projectPath, 0775, true)) {
        throw new Exception("Failed to create project directory: {$this->projectPath}");
    }
    
    $success = chgrp($this->projectPath, 'devs');
   
    chown($this->projectPath, 'www-data');
    chgrp($this->projectPath, 'devs');
}

function safeCreateDirectory(string $path): void {
    $realWhim = realpath(WHIM_ROOT);
    $normalized = rtrim($path, '/');
    $resolved = realpath($normalized) ?: $normalized;

    error_log("[WHIM DEBUG] Comparing path: $resolved vs WHIM_ROOT: $realWhim");

    if ($realWhim && str_starts_with($resolved, $realWhim)) {
        error_log("[WHIM GenUtil::safeCreateDir] 🚫 Refused to create directory inside WHIM: $resolved");
        return;
    }

    if (!is_dir($normalized)) {
        if (!mkdir($normalized, 0775, true)) {
            error_log("[WHIM GenUtil::safeCreateDir] ❌ mkdir failed for: $normalized");
        } else {
            if (posix_geteuid() === 0) {
                chown($normalized, WHIM_OWNER);
                chgrp($normalized, WHIM_GROUP);
            }
            error_log("[WHIM GenUtil::safeCreateDir] ✅ Created: $normalized");
        }
    }
}

function flattenMetadata(array $template): array {
    $flat = [];
    foreach ($template as $key => $field) {
        if (is_array($field) && array_key_exists('value', $field)) {
            $flat[$key] = $field['value'];
        } else {
            $flat[$key] = $field;
        }
    }
    return $flat;
}


function syncWpSiteUrls(Project $project): void {
    $domain = $project->get('domain');
    $url = "https://{$domain}.dev.local";
    $wpPath = $project->getPath();

    $cmd = "sudo -u www-data wp option update home '$url' --path='$wpPath' && " .
           "sudo -u www-data wp option update siteurl '$url' --path='$wpPath'";

    $output = shell_exec($cmd);
    if ($output === null) {
        error_log("[UTIL syncWpSiteUrls] 🔧 wp output: (null)");
    } else {
        error_log("[UTIL syncWpSiteUrls] 🔧 wp output: " . trim($output));
    }
    error_log("[UTIL syncWpSiteUrls] ✅ Patched WP URL to $url");
  
}

