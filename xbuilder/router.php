<?php
/**
 * XBuilder Admin Router
 *
 * Handles routing for the XBuilder admin interface.
 * Routes to appropriate views or API endpoints.
 *
 * Combined best practices:
 * - Manual requires for core classes (explicit dependencies)
 * - Instance-based class usage
 * - Clean route definitions
 */

// Load core classes
require_once __DIR__ . '/core/Security.php';
require_once __DIR__ . '/core/Config.php';
require_once __DIR__ . '/core/AI.php';
require_once __DIR__ . '/core/Conversation.php';
require_once __DIR__ . '/core/Generator.php';

use XBuilder\Core\Config;
use XBuilder\Core\Security;
use XBuilder\Core\Conversation;
use XBuilder\Core\Generator;

// Initialize core services
$security = new Security();
$config = new Config();

// Get the request path relative to /xbuilder/
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$path = preg_replace('#^/xbuilder#', '', $path);
$path = rtrim($path, '/') ?: '/';

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Define routes
$routes = [
    // Views (GET requests)
    'GET' => [
        '/' => 'handleDashboard',
        '/setup' => 'handleSetupView',
        '/login' => 'handleLoginView',
        '/chat' => 'handleChatView',
        '/logout' => 'handleLogout',
        '/preview' => 'handlePreview',
    ],
    // API endpoints (POST requests)
    'POST' => [
        '/api/setup' => 'handleSetupApi',
        '/api/login' => 'handleLoginApi',
        '/api/chat' => 'handleChatApi',
        '/api/upload' => 'handleUploadApi',
        '/api/publish' => 'handlePublishApi',
        '/api/clear' => 'handleClearApi',
    ],
];

// Route the request
if (isset($routes[$method][$path])) {
    $handler = $routes[$method][$path];
    $handler($config, $security);
} else {
    // 404 Not Found
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not found']);
}

/**
 * Handle dashboard/home - redirect appropriately
 */
function handleDashboard(Config $config, Security $security): void
{
    // Check if setup is complete
    if (!$config->isSetupComplete()) {
        header('Location: /xbuilder/setup');
        exit;
    }

    // Check if authenticated
    if (!$security->isAuthenticated()) {
        header('Location: /xbuilder/login');
        exit;
    }

    // Redirect to chat interface
    header('Location: /xbuilder/chat');
    exit;
}

/**
 * Handle setup wizard view
 */
function handleSetupView(Config $config, Security $security): void
{
    // If already set up, redirect to login
    if ($config->isSetupComplete()) {
        header('Location: /xbuilder/login');
        exit;
    }

    // Make security available to view for CSRF token
    require __DIR__ . '/views/setup.php';
}

/**
 * Handle login view
 */
function handleLoginView(Config $config, Security $security): void
{
    // If not set up, redirect to setup
    if (!$config->isSetupComplete()) {
        header('Location: /xbuilder/setup');
        exit;
    }

    // If already authenticated, redirect to chat
    if ($security->isAuthenticated()) {
        header('Location: /xbuilder/chat');
        exit;
    }

    require __DIR__ . '/views/login.php';
}

/**
 * Handle chat interface view
 */
function handleChatView(Config $config, Security $security): void
{
    // If not set up, redirect to setup
    if (!$config->isSetupComplete()) {
        header('Location: /xbuilder/setup');
        exit;
    }

    // Require authentication
    if (!$security->isAuthenticated()) {
        header('Location: /xbuilder/login');
        exit;
    }

    require __DIR__ . '/views/chat.php';
}

/**
 * Handle logout
 */
function handleLogout(Config $config, Security $security): void
{
    $security->logout();
    header('Location: /xbuilder/login');
    exit;
}

/**
 * Handle preview page
 */
function handlePreview(Config $config, Security $security): void
{
    if (!$security->isAuthenticated()) {
        header('Location: /xbuilder/login');
        exit;
    }

    $generator = new Generator();
    $previewPath = $generator->getPreviewPath();

    if (file_exists($previewPath)) {
        header('Content-Type: text/html; charset=UTF-8');
        readfile($previewPath);
    } else {
        http_response_code(404);
        echo '<!DOCTYPE html><html><head><title>No Preview</title></head>';
        echo '<body style="font-family: system-ui, sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; background: #1a1a2e; color: #eee;">';
        echo '<div style="text-align: center;"><h1>No Preview Available</h1><p>Start chatting to generate your website</p></div>';
        echo '</body></html>';
    }
    exit;
}

/**
 * Handle setup API
 */
function handleSetupApi(Config $config, Security $security): void
{
    // Pass dependencies to API
    $GLOBALS['xbuilder_config'] = $config;
    $GLOBALS['xbuilder_security'] = $security;
    require __DIR__ . '/api/setup.php';
}

/**
 * Handle login API
 */
function handleLoginApi(Config $config, Security $security): void
{
    $GLOBALS['xbuilder_config'] = $config;
    $GLOBALS['xbuilder_security'] = $security;
    require __DIR__ . '/api/login.php';
}

/**
 * Handle chat API
 */
function handleChatApi(Config $config, Security $security): void
{
    $GLOBALS['xbuilder_config'] = $config;
    $GLOBALS['xbuilder_security'] = $security;
    require __DIR__ . '/api/chat.php';
}

/**
 * Handle upload API
 */
function handleUploadApi(Config $config, Security $security): void
{
    $GLOBALS['xbuilder_config'] = $config;
    $GLOBALS['xbuilder_security'] = $security;
    require __DIR__ . '/api/upload.php';
}

/**
 * Handle publish API
 */
function handlePublishApi(Config $config, Security $security): void
{
    $GLOBALS['xbuilder_config'] = $config;
    $GLOBALS['xbuilder_security'] = $security;
    require __DIR__ . '/api/publish.php';
}

/**
 * Handle clear conversation API
 */
function handleClearApi(Config $config, Security $security): void
{
    header('Content-Type: application/json');

    if (!$security->isAuthenticated()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $conversation = new Conversation();
    $conversation->clear();

    $generator = new Generator();
    $generator->deletePreview();

    echo json_encode(['success' => true]);
}
