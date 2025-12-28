<?php
/**
 * XBuilder Setup API
 * 
 * Handles initial setup: API key validation and account creation
 */

header('Content-Type: application/json');

require_once dirname(__DIR__) . '/core/Security.php';
require_once dirname(__DIR__) . '/core/Config.php';
require_once dirname(__DIR__) . '/core/AI.php';

use XBuilder\Core\Security;
use XBuilder\Core\Config;
use XBuilder\Core\AI;

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$security = new Security();
$config = new Config();

switch ($input['action']) {
    case 'validate_key':
        // Validate API key
        if (!isset($input['provider']) || !isset($input['api_key'])) {
            echo json_encode(['valid' => false, 'error' => 'Missing provider or API key']);
            exit;
        }
        
        $provider = $input['provider'];
        $apiKey = trim($input['api_key']);
        
        // Basic format validation
        if (!$security->validateApiKeyFormat($provider, $apiKey)) {
            echo json_encode(['valid' => false, 'error' => 'Invalid API key format']);
            exit;
        }
        
        // Store key temporarily for validation
        $security->storeApiKey($provider, $apiKey);
        
        // Test the key with actual API call
        $ai = new AI($provider);
        $result = $ai->testApiKey();
        
        if (!$result['valid']) {
            // Remove invalid key
            $security->deleteApiKey($provider);
            echo json_encode(['valid' => false, 'error' => $result['error'] ?? 'API key validation failed']);
            exit;
        }
        
        echo json_encode(['valid' => true]);
        break;
        
    case 'complete':
        // Complete setup
        if (!isset($input['provider']) || !isset($input['api_key']) || !isset($input['password'])) {
            echo json_encode(['success' => false, 'error' => 'Missing required fields']);
            exit;
        }
        
        $provider = $input['provider'];
        $apiKey = trim($input['api_key']);
        $password = $input['password'];
        
        // Validate password
        if (strlen($password) < 8) {
            echo json_encode(['success' => false, 'error' => 'Password must be at least 8 characters']);
            exit;
        }
        
        // Store API key
        if (!$security->storeApiKey($provider, $apiKey)) {
            echo json_encode(['success' => false, 'error' => 'Failed to store API key']);
            exit;
        }
        
        // Hash password and complete setup
        $passwordHash = $security->hashPassword($password);
        
        if (!$config->completeSetup($passwordHash, $provider)) {
            echo json_encode(['success' => false, 'error' => 'Failed to save configuration']);
            exit;
        }
        
        // Auto-login after setup
        $security->setAuthenticated(true);
        
        echo json_encode(['success' => true]);
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unknown action']);
}
