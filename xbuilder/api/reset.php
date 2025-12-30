<?php
/**
 * Reset API Endpoint
 *
 * Deletes all XBuilder data and returns to setup wizard
 */

require_once __DIR__ . '/../core/Security.php';

use XBuilder\Core\Security;

header('Content-Type: application/json');

// Initialize security
$security = new Security();

// Require authentication
if (!$security->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get request data
$input = json_decode(file_get_contents('php://input'), true);
$confirm = $input['confirm'] ?? '';

// Require explicit confirmation
if ($confirm !== 'RESET_XBUILDER') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Reset confirmation required'
    ]);
    exit;
}

try {
    $storagePath = dirname(__DIR__) . '/storage';
    $sitePath = dirname(__DIR__, 2) . '/site';

    // Delete all API keys
    $keysPath = $storagePath . '/keys';
    if (is_dir($keysPath)) {
        $files = glob($keysPath . '/*.key');
        foreach ($files as $file) {
            if (is_file($file)) {
                // Secure deletion: overwrite then delete
                $size = filesize($file);
                file_put_contents($file, random_bytes($size));
                unlink($file);
            }
        }
    }

    // Delete config
    $configFile = $storagePath . '/config.json';
    if (file_exists($configFile)) {
        unlink($configFile);
    }

    // Delete all conversations
    $conversationsPath = $storagePath . '/conversations';
    if (is_dir($conversationsPath)) {
        $files = glob($conversationsPath . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    // Delete all uploads
    $uploadsPath = $storagePath . '/uploads';
    if (is_dir($uploadsPath)) {
        $files = glob($uploadsPath . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    // Delete generated site
    $siteFile = $sitePath . '/index.html';
    if (file_exists($siteFile)) {
        unlink($siteFile);
    }

    // Delete backups
    $backupsPath = $storagePath . '/backups';
    if (is_dir($backupsPath)) {
        $files = glob($backupsPath . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    // Delete security log
    $securityLog = $storagePath . '/security.log';
    if (file_exists($securityLog)) {
        unlink($securityLog);
    }

    // Delete password file
    $passwordFile = $storagePath . '/password.hash';
    if (file_exists($passwordFile)) {
        unlink($passwordFile);
    }

    // Clear session
    $security->logout();
    session_destroy();

    echo json_encode([
        'success' => true,
        'message' => 'XBuilder has been reset successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to reset: ' . $e->getMessage()
    ]);
}
