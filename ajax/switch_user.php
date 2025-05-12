<?php
declare(strict_types=1);
require_once __DIR__ . '/../bootstrap.php';


header('Content-Type: application/json');
ini_set('display_errors', '0');
error_reporting(0);

try {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (!is_array($data)) {
        throw new RuntimeException("Invalid input");
    }

    if (empty($data['username'])) {
        throw new RuntimeException("No username provided");
    }

    $username = $data['username'];

    // Validate user exists
    $allowedUsers = ['john', 'chuck'];
    if (!in_array($username, $allowedUsers, true)) {
        throw new RuntimeException("Unauthorized user");
    }

    // Set the cookie
    setcookie('whim_active_user', $username, [
        'path' => '/',
        'expires' => time() + (86400 * 30), // 30 days
        'secure' => false, // Set to true if HTTPS
        'httponly' => true,
        'samesite' => 'Strict'
    ]);

    echo json_encode(['success' => true]);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    exit;
}
