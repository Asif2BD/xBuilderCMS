<?php
/**
 * XBuilder - AI-Powered Website Generator
 * 
 * Main entry point that routes between:
 * - The generated static site (/)
 * - The XBuilder admin interface (/xbuilder/)
 */

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Start session for admin authentication
session_start();

// Constants
define('XBUILDER_ROOT', __DIR__);
define('XBUILDER_STORAGE', __DIR__ . '/xbuilder/storage');
define('XBUILDER_SITE', __DIR__ . '/site');

// Get the request path
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$path = rtrim($path, '/');

// Check if this is an xbuilder admin request
if (strpos($path, '/xbuilder') === 0 || $path === '/xbuilder') {
    require __DIR__ . '/xbuilder/router.php';
    exit;
}

// Check if a site has been generated
$siteIndexExists = file_exists(XBUILDER_SITE . '/index.html');
$setupComplete = file_exists(XBUILDER_STORAGE . '/config.json');

// If no site exists, redirect to setup or builder
if (!$siteIndexExists) {
    if (!$setupComplete) {
        header('Location: /xbuilder/setup');
    } else {
        header('Location: /xbuilder/');
    }
    exit;
}

// Serve the generated static site
$requestedFile = $path === '' || $path === '/' 
    ? '/index.html' 
    : $path;

// Add .html extension if no extension present
if (!pathinfo($requestedFile, PATHINFO_EXTENSION)) {
    $requestedFile .= '.html';
}

$filePath = XBUILDER_SITE . $requestedFile;

// Security: prevent directory traversal
$realPath = realpath($filePath);
$siteRealPath = realpath(XBUILDER_SITE);

if ($realPath && strpos($realPath, $siteRealPath) === 0 && file_exists($realPath)) {
    // Determine content type
    $ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
    $contentTypes = [
        'html' => 'text/html; charset=UTF-8',
        'css' => 'text/css; charset=UTF-8',
        'js' => 'application/javascript; charset=UTF-8',
        'json' => 'application/json; charset=UTF-8',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'webp' => 'image/webp',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject',
    ];
    
    $contentType = $contentTypes[$ext] ?? 'application/octet-stream';
    
    // Set caching headers for assets
    if (in_array($ext, ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'woff', 'woff2'])) {
        header('Cache-Control: public, max-age=31536000');
    }
    
    header('Content-Type: ' . $contentType);
    header('Content-Length: ' . filesize($realPath));
    readfile($realPath);
    exit;
}

// 404 - File not found
http_response_code(404);

// Check for custom 404 page
if (file_exists(XBUILDER_SITE . '/404.html')) {
    header('Content-Type: text/html; charset=UTF-8');
    readfile(XBUILDER_SITE . '/404.html');
} else {
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #0f172a; 
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
        }
        h1 { font-size: 6rem; font-weight: 700; color: #3b82f6; }
        p { font-size: 1.25rem; margin: 1rem 0 2rem; color: #94a3b8; }
        a { 
            color: #3b82f6; 
            text-decoration: none;
            padding: 0.75rem 2rem;
            border: 2px solid #3b82f6;
            border-radius: 9999px;
            transition: all 0.2s;
        }
        a:hover { background: #3b82f6; color: white; }
    </style>
</head>
<body>
    <div>
        <h1>404</h1>
        <p>The page you\'re looking for doesn\'t exist.</p>
        <a href="/">Go Home</a>
    </div>
</body>
</html>';
}
