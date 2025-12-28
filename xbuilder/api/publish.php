<?php
/**
 * XBuilder Publish API
 *
 * Publishes the preview to the live site.
 */

use XBuilder\Core\Security;
use XBuilder\Core\Generator;

header('Content-Type: application/json');

// Require authentication
if (!Security::isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Verify CSRF token
$csrfToken = $_POST['csrf_token'] ?? '';
if (!Security::verifyCsrfToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid security token']);
    exit;
}

try {
    // Check if there's a preview to publish
    if (!Generator::hasPreview()) {
        throw new \RuntimeException('No preview to publish');
    }

    // Publish the site
    Generator::publish();

    echo json_encode([
        'success' => true,
        'message' => 'Website published successfully',
        'url' => '/'
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
