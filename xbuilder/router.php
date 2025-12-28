<?php
/**
 * XBuilder Admin Router
 *
 * Handles routing for the XBuilder admin interface.
 * Routes to appropriate views or API endpoints.
 */

// Autoloader for XBuilder classes
spl_autoload_register(function ($class) {
    // Only handle XBuilder namespace
    if (strpos($class, 'XBuilder\\') !== 0) {
        return;
    }

    // Convert namespace to file path
    $class = str_replace('XBuilder\\', '', $class);
    $class = str_replace('\\', '/', $class);
    $file = __DIR__ . '/' . strtolower($class) . '.php';

    // Handle Core namespace
    $file = str_replace('/core/', '/core/', $file);

    if (file_exists($file)) {
        require_once $file;
    }
});

use XBuilder\Core\Config;
use XBuilder\Core\Security;

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
    $handler();
} else {
    // 404 Not Found
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
}

/**
 * Handle dashboard/home - redirect appropriately
 */
function handleDashboard(): void
{
    // Check if setup is complete
    if (!Config::isSetupComplete()) {
        header('Location: /xbuilder/setup');
        exit;
    }

    // Check if authenticated
    if (!Security::isAuthenticated()) {
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
function handleSetupView(): void
{
    // If already set up, redirect to login
    if (Config::isSetupComplete()) {
        header('Location: /xbuilder/login');
        exit;
    }

    require __DIR__ . '/views/setup.php';
}

/**
 * Handle login view
 */
function handleLoginView(): void
{
    // If not set up, redirect to setup
    if (!Config::isSetupComplete()) {
        header('Location: /xbuilder/setup');
        exit;
    }

    // If already authenticated, redirect to chat
    if (Security::isAuthenticated()) {
        header('Location: /xbuilder/chat');
        exit;
    }

    require __DIR__ . '/views/login.php';
}

/**
 * Handle chat interface view
 */
function handleChatView(): void
{
    // If not set up, redirect to setup
    if (!Config::isSetupComplete()) {
        header('Location: /xbuilder/setup');
        exit;
    }

    // Require authentication
    Security::requireAuth();

    require __DIR__ . '/views/chat.php';
}

/**
 * Handle logout
 */
function handleLogout(): void
{
    Security::logout();
    header('Location: /xbuilder/login');
    exit;
}

/**
 * Handle preview page
 */
function handlePreview(): void
{
    Security::requireAuth();

    $previewPath = XBUILDER_ROOT . '/site/preview.html';

    if (file_exists($previewPath)) {
        header('Content-Type: text/html; charset=UTF-8');
        readfile($previewPath);
    } else {
        http_response_code(404);
        echo '<!DOCTYPE html><html><head><title>No Preview</title></head><body style="font-family: sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; background: #1a1a2e; color: #eee;"><div style="text-align: center;"><h1>No Preview Available</h1><p>Start chatting to generate your website</p></div></body></html>';
    }
    exit;
}

/**
 * Handle setup API
 */
function handleSetupApi(): void
{
    require __DIR__ . '/api/setup.php';
}

/**
 * Handle login API
 */
function handleLoginApi(): void
{
    require __DIR__ . '/api/login.php';
}

/**
 * Handle chat API
 */
function handleChatApi(): void
{
    require __DIR__ . '/api/chat.php';
}

/**
 * Handle upload API
 */
function handleUploadApi(): void
{
    require __DIR__ . '/api/upload.php';
}

/**
 * Handle publish API
 */
function handlePublishApi(): void
{
    require __DIR__ . '/api/publish.php';
}

/**
 * Handle clear conversation API
 */
function handleClearApi(): void
{
    header('Content-Type: application/json');

    if (!Security::isAuthenticated()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    use XBuilder\Core\Conversation;
    use XBuilder\Core\Generator;

    $conversation = new Conversation();
    $conversation->clear();
    Generator::deletePreview();

    echo json_encode(['success' => true]);
}
