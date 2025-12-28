<?php
/**
 * XBuilder Login API
 *
 * Handles admin authentication.
 */

use XBuilder\Core\Config;
use XBuilder\Core\Security;

header('Content-Type: application/json');

// Check if setup is complete
if (!Config::isSetupComplete()) {
    http_response_code(400);
    echo json_encode(['error' => 'Setup not complete', 'redirect' => '/xbuilder/setup']);
    exit;
}

// Verify CSRF token
$csrfToken = $_POST['csrf_token'] ?? '';
if (!Security::verifyCsrfToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid security token']);
    exit;
}

// Get password
$password = $_POST['password'] ?? '';

if (empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Password is required']);
    exit;
}

// Attempt authentication
if (Security::authenticate($password)) {
    echo json_encode([
        'success' => true,
        'redirect' => '/xbuilder/chat'
    ]);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid password']);
}
