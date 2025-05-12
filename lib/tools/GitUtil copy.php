<?php
declare(strict_types=1);

// Git Functions

function gitExec(Project $project, string $cmd, ?string $repo = null): string {
    $repoPath = $repo ?? realpath($project->path);

    if (!$repoPath || !is_dir($repoPath)) {
        throw new RuntimeException("Invalid repository path: $repoPath");
    }

    $fullCmd = "cd " . escapeshellarg($repoPath) . " && git $cmd 2>&1";

    exec($fullCmd, $output, $exitCode);
    $result = implode("\n", $output);

    if ($exitCode !== 0) {
        throw new RuntimeException("[gitExec] ❌ Command failed: $fullCmd\n$result");
    }

    return $result;
}


function gitGetCurrentBranch(Project $project): string {
    return trim(gitExec($project, "rev-parse --abbrev-ref HEAD"));
}

function gitSwitchBranch(Project $project, string $branch, bool $createIfMissing = false): void {
    try {
        gitExec($project, "checkout $branch");
    } catch (RuntimeException $e) {
        if ($createIfMissing) {
            // Check if it exists first
            $branches = gitExec($project, "branch --list $branch");

            if (trim($branches) === '') {
                gitExec($project, "checkout -b $branch");
            } else {
                // Branch exists, rethrow the original error
                throw new RuntimeException("Branch '$branch' already exists but checkout failed: " . $e->getMessage());
            }
        } else {
            throw $e;
        }
    }
}


function gitInitRepo(Project $project, string $template = 'generic'): void {
    $repoPath = rtrim($project->getDevelopmentDirectory(), '/');

    // ✅ Protect against WHIM corruption
    if (strpos(realpath($repoPath), realpath(WHIM_ROOT)) === 0) {
        error_log("[WHIM GitUtil->gitInitRepo] 🚫 Refusing to initialize Git inside WHIM.");
        return;
    }

    // ✅ Initialize Git repo
    gitExec($project, "init --initial-branch=main");

    // ✅ Configure user if needed
    try {
        $currentUser = trim(gitExec($project, "config --get user.name"));
        $currentEmail = trim(gitExec($project, "config --get user.email"));
    } catch (Throwable $e) {
        error_log("[WHIM GitUtil->gitInitRepo] No Git identity set. Applying fallback.");
        $currentUser = 'www-data';
        $currentEmail = 'www-data@localhost';
        gitExec($project, "config user.name '$currentUser'");
        gitExec($project, "config user.email '$currentEmail'");
    }

    // ✅ Copy .gitignore template
    $source = TEMPLATE_DIR . "gitignore-{$template}";
    $target = $repoPath . '/.gitignore';
    if (!copy($source, $target)) {
        throw new Exception("Failed to copy .gitignore from template: $template");
    }

    // ✅ First commit and branch setup
    gitExec($project, "add .gitignore");
    gitExec($project, "commit -m 'Initial commit [WHIM]'");
    gitExec($project, "checkout -b rel");
    gitExec($project, "checkout -b dev");

    // ✅ Mark directory safe
    gitExec($project, "config --global --add safe.directory '{$repoPath}'");
}

function gitCommit(Project $project, string $message, ?string $repo = null): string {
    $repoPath = $repo ?? realpath($project->path);

    if (strpos($repoPath, realpath(WHIM_ROOT)) === 0) {
        error_log("[WHIM GitUtil->gitCommit] 🚫 Skipping Git commit inside WHIM project: $repoPath");
        return '(WHIM project — commit skipped)';
    }

    $status = trim(gitExec($project, "status --porcelain", $repoPath));
    if ($status === '') {
        return '(no changes to commit)';
    }

    $output = [];
    $output[] = gitExec($project, 'add -A', $repoPath);
    $output[] = gitExec($project, 'commit -m ' . escapeshellarg($message), $repoPath);

    return implode("\n", $output);
}


function gitCurrentStatus(): string {
    return gitExec($project, "status --short");
}
