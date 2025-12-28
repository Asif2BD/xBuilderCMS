<?php
/**
 * XBuilder Admin Router
 * 
 * Handles all /xbuilder/* routes for the admin interface
 */

// Load core classes
require_once __DIR__ . '/core/Security.php';
require_once __DIR__ . '/core/Config.php';
require_once __DIR__ . '/core/AI.php';
require_once __DIR__ . '/core/Generator.php';
require_once __DIR__ . '/core/Conversation.php';

use XBuilder\Core\Security;
use XBuilder\Core\Config;

// Initialize
$config = new Config();
$security = new Security();

// Get the route (remove /xbuilder prefix)
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$route = str_replace('/xbuilder', '', $path);
$route = trim($route, '/') ?: 'home';

// Check if setup is complete
$setupComplete = $config->isSetupComplete();

// Public routes (no auth needed)
$publicRoutes = ['setup', 'api/setup'];

// If setup not complete, only allow setup routes
if (!$setupComplete && !in_array($route, $publicRoutes)) {
    header('Location: /xbuilder/setup');
    exit;
}

// If setup complete and trying to access setup, redirect to home
if ($setupComplete && $route === 'setup') {
    header('Location: /xbuilder/');
    exit;
}

// Protected routes need authentication
$protectedRoutes = ['home', 'api/chat', 'api/generate', 'api/settings', 'api/upload', 'api/preview'];

if (in_array($route, $protectedRoutes) || strpos($route, 'api/') === 0) {
    // Check authentication (skip for initial setup)
    if ($setupComplete && !$security->isAuthenticated()) {
        if (strpos($route, 'api/') === 0) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        header('Location: /xbuilder/login');
        exit;
    }
}

// Route to appropriate handler
switch ($route) {
    case 'setup':
        require __DIR__ . '/views/setup.php';
        break;
        
    case 'login':
        require __DIR__ . '/views/login.php';
        break;
        
    case 'logout':
        $security->logout();
        header('Location: /xbuilder/login');
        break;
        
    case 'home':
    case '':
        require __DIR__ . '/views/chat.php';
        break;
        
    // API Routes
    case 'api/setup':
        require __DIR__ . '/api/setup.php';
        break;
        
    case 'api/login':
        require __DIR__ . '/api/login.php';
        break;
        
    case 'api/chat':
        require __DIR__ . '/api/chat.php';
        break;
        
    case 'api/upload':
        require __DIR__ . '/api/upload.php';
        break;
        
    case 'api/generate':
        require __DIR__ . '/api/generate.php';
        break;
        
    case 'api/preview':
        require __DIR__ . '/api/preview.php';
        break;
        
    case 'api/settings':
        require __DIR__ . '/api/settings.php';
        break;
        
    case 'api/publish':
        require __DIR__ . '/api/publish.php';
        break;
        
    default:
        // Check if it's a static asset
        $assetPath = __DIR__ . '/assets/' . $route;
        if (file_exists($assetPath) && is_file($assetPath)) {
            $ext = pathinfo($assetPath, PATHINFO_EXTENSION);
            $types = [
                'css' => 'text/css',
                'js' => 'application/javascript',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'svg' => 'image/svg+xml'
            ];
            header('Content-Type: ' . ($types[$ext] ?? 'text/plain'));
            readfile($assetPath);
            exit;
        }
        
        // 404 for unknown routes
        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
}
