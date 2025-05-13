<?php
require_once __DIR__ . '/bootstrap.php';

$project = Project::getByName('squarepegartstudio');
if (!$project) {
    error_log("❌ Project 'squarepegartstudio' not found");
    exit(1);
}

ensureHttpVHost($project);
