<?php
/**
 * Update API Endpoint
 *
 * Handles:
 * - Checking for updates
 * - Performing updates
 * - Listing backups
 * - Rolling back to backup
 */

require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../core/Update.php';

use XBuilder\Core\Security;
use XBuilder\Core\Update;

header('Content-Type: application/json');

// Initialize security
$security = new Security();

// Require authentication
if (!$security->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get request data
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

$update = new Update();

switch ($action) {
    case 'check':
        // Check for updates
        $result = $update->checkForUpdates();
        echo json_encode($result);
        break;

    case 'perform':
        // Perform update (this may take a while)
        set_time_limit(300); // 5 minutes
        $result = $update->performUpdate();

        // Clean old backups after successful update
        if ($result['success']) {
            $update->cleanOldBackups();
        }

        echo json_encode($result);
        break;

    case 'list_backups':
        // List available backups
        $backups = $update->getBackups();
        echo json_encode([
            'success' => true,
            'backups' => $backups
        ]);
        break;

    case 'rollback':
        // Rollback to specific backup
        $backupFile = $input['backup_file'] ?? '';
        if (empty($backupFile)) {
            echo json_encode([
                'success' => false,
                'error' => 'Backup file not specified'
            ]);
            break;
        }

        $backupPath = dirname(__DIR__) . '/storage/backups/' . basename($backupFile);
        $result = $update->rollback($backupPath);

        echo json_encode([
            'success' => $result,
            'error' => $result ? null : 'Failed to rollback'
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}
