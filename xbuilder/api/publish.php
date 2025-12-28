<?php
/**
 * XBuilder Publish API
 *
 * Publishes the preview site to production.
 *
 * Variables available from router:
 * - $GLOBALS['xbuilder_config']: Config instance
 * - $GLOBALS['xbuilder_security']: Security instance
 */

use XBuilder\Core\Generator;

header('Content-Type: application/json');

$config = $GLOBALS['xbuilder_config'];
$security = $GLOBALS['xbuilder_security'];

// Check authentication
if (!$security->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Verify CSRF token
$csrfToken = $_POST['csrf_token'] ?? '';
if (!$security->verifyCsrfToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid security token']);
    exit;
}

try {
    $generator = new Generator();

    // Check if preview exists
    if (!$generator->hasPreview()) {
        throw new \RuntimeException('No preview to publish. Generate a website first.');
    }

    // Publish (this will create a backup if a site already exists)
    $generator->publish();

    // Update config
    $config->setSiteGenerated(true);

    // Clean old backups (keep last 10)
    $generator->cleanOldBackups(10);

    // Get stats for response
    $stats = $generator->getStats();

    echo json_encode([
        'success' => true,
        'message' => 'Website published successfully!',
        'url' => '/',
        'stats' => $stats
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
