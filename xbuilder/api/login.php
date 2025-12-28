<?php
/**
 * XBuilder Login API
 *
 * Handles admin authentication.
 *
 * Variables available from router:
 * - $GLOBALS['xbuilder_config']: Config instance
 * - $GLOBALS['xbuilder_security']: Security instance
 */

header('Content-Type: application/json');

$config = $GLOBALS['xbuilder_config'];
$security = $GLOBALS['xbuilder_security'];

// Check if setup is complete
if (!$config->isSetupComplete()) {
    http_response_code(400);
    echo json_encode(['error' => 'Setup not complete', 'redirect' => '/xbuilder/setup']);
    exit;
}

// Verify CSRF token
$csrfToken = $_POST['csrf_token'] ?? '';
if (!$security->verifyCsrfToken($csrfToken)) {
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

// Get stored password hash
$passwordHash = $config->getPasswordHash();
if (!$passwordHash) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error']);
    exit;
}

// Attempt authentication
if ($security->authenticate($password, $passwordHash)) {
    echo json_encode([
        'success' => true,
        'redirect' => '/xbuilder/chat'
    ]);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid password']);
}
