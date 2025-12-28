<?php
/**
 * XBuilder Setup API
 *
 * Handles initial configuration:
 * - AI provider selection
 * - API key storage (encrypted)
 * - Admin password creation
 */

use XBuilder\Core\Config;
use XBuilder\Core\Security;

header('Content-Type: application/json');

// Check if already set up
if (Config::isSetupComplete()) {
    http_response_code(400);
    echo json_encode(['error' => 'Setup already complete']);
    exit;
}

// Verify CSRF token
$csrfToken = $_POST['csrf_token'] ?? '';
if (!Security::verifyCsrfToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid security token']);
    exit;
}

// Get and validate inputs
$provider = $_POST['provider'] ?? '';
$apiKey = $_POST['api_key'] ?? '';
$password = $_POST['password'] ?? '';

// Validate provider
$validProviders = ['claude', 'openai', 'gemini'];
if (!in_array($provider, $validProviders)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid AI provider']);
    exit;
}

// Validate API key
$apiKey = trim($apiKey);
if (empty($apiKey)) {
    http_response_code(400);
    echo json_encode(['error' => 'API key is required']);
    exit;
}

if (!Security::validateApiKeyFormat($provider, $apiKey)) {
    http_response_code(400);
    $hints = [
        'claude' => 'Claude API keys start with "sk-ant-"',
        'openai' => 'OpenAI API keys start with "sk-"',
        'gemini' => 'Gemini API keys are alphanumeric strings (~39 characters)',
    ];
    echo json_encode(['error' => 'Invalid API key format. ' . ($hints[$provider] ?? '')]);
    exit;
}

// Validate password
if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['error' => 'Password must be at least 8 characters']);
    exit;
}

try {
    // Run setup
    Config::setup($provider, $apiKey, $password);

    // Auto-login after setup
    Security::authenticate($password);

    echo json_encode([
        'success' => true,
        'message' => 'Setup complete'
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
