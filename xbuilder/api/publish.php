<?php
/**
 * XBuilder Publish API
 * 
 * Publishes the generated site to the web root
 */

header('Content-Type: application/json');

require_once dirname(__DIR__) . '/core/Generator.php';
require_once dirname(__DIR__) . '/core/Config.php';

use XBuilder\Core\Generator;
use XBuilder\Core\Config;

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['html'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'HTML content required']);
    exit;
}

$html = $input['html'];

if (empty(trim($html))) {
    echo json_encode(['success' => false, 'error' => 'HTML content cannot be empty']);
    exit;
}

$generator = new Generator();
$config = new Config();

// Save the HTML
$result = $generator->saveHtml($html, 'index.html');

if (!$result['success']) {
    echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Failed to save site']);
    exit;
}

// Update config to mark site as generated
$config->markSiteGenerated();

// Store metadata
$config->setSiteMetadata([
    'published_at' => date('c'),
    'file_size' => $result['size']
]);

echo json_encode([
    'success' => true,
    'url' => '/',
    'size' => $result['size']
]);
