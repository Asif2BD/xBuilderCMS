<?php
/**
 * XBuilder Login API
 */

header('Content-Type: application/json');

require_once dirname(__DIR__) . '/core/Security.php';
require_once dirname(__DIR__) . '/core/Config.php';

use XBuilder\Core\Security;
use XBuilder\Core\Config;

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Password required']);
    exit;
}

$security = new Security();
$config = new Config();

$passwordHash = $config->getPasswordHash();

if (!$passwordHash) {
    echo json_encode(['success' => false, 'error' => 'Setup not complete']);
    exit;
}

if ($security->verifyPassword($input['password'], $passwordHash)) {
    $security->setAuthenticated(true);
    echo json_encode(['success' => true]);
} else {
    // Add small delay to prevent brute force
    usleep(500000); // 0.5 second
    echo json_encode(['success' => false, 'error' => 'Invalid password']);
}
