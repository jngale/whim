<?php

require_once WHIM_ROOT . 'lib/Project.php';

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

function gitSwitchBranch(Project $project, string $branch, ?string $repo = null): void {
    $repoPath = $repo ?? realpath($project->path);
    gitExec($project, "checkout $branch", $repoPath);
}

function gitSwitchOrCreateBranchIfMissing(Project $project, string $branch, ?string $repo = null): void {
    $repoPath = $repo ?? realpath($project->path);
    $branches = gitExec($project, "branch", $repoPath);

    if (strpos($branches, $branch) === false) {
        gitExec($project, "checkout -b $branch", $repoPath);
    } else {
        gitSwitchBranch($project, $branch, $repoPath);
    }
}

function gitGetCurrentBranch(Project $project, ?string $repo = null): string {
    $repoPath = $repo ?? realpath($project->path);
    return trim(gitExec($project, "rev-parse --abbrev-ref HEAD", $repoPath));
}
