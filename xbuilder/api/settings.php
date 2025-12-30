<?php
/**
 * Settings API Endpoint
 *
 * Handles:
 * - Viewing current AI provider and model
 * - Switching AI provider
 * - Switching AI model
 * - Managing API keys (list, add, delete)
 */

require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../core/Config.php';

use XBuilder\Core\Security;
use XBuilder\Core\Config;

header('Content-Type: application/json');

// Initialize
$security = new Security();
$config = new Config();

// Require authentication
if (!$security->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get request data
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch ($action) {
    case 'get_current':
        // Get current AI settings
        $currentProvider = $config->get('ai_provider', 'gemini');
        $currentModel = $config->get('ai_model');

        // Get available providers and their API key status
        $providers = [
            'gemini' => [
                'name' => 'Google Gemini',
                'has_key' => $security->hasApiKey('gemini'),
                'models' => [
                    'gemini-2.0-flash-exp' => 'Gemini 2.0 Flash (Free - Experimental)',
                    'gemini-2.0-flash-thinking-exp-01-21' => 'Gemini 2.0 Flash Thinking (Free)',
                    'gemini-1.5-flash' => 'Gemini 1.5 Flash (Free)',
                    'gemini-1.5-flash-8b' => 'Gemini 1.5 Flash-8B (Free)',
                    'gemini-1.5-pro' => 'Gemini 1.5 Pro (Paid)',
                    'gemini-exp-1206' => 'Gemini Experimental 1206 (Free)'
                ]
            ],
            'claude' => [
                'name' => 'Anthropic Claude',
                'has_key' => $security->hasApiKey('claude'),
                'models' => [
                    'claude-sonnet-4-20250514' => 'Claude Sonnet 4 (Latest)',
                    'claude-opus-4-20250514' => 'Claude Opus 4 (Most Capable)',
                    'claude-3-7-sonnet-20250219' => 'Claude 3.7 Sonnet',
                    'claude-3-5-sonnet-20241022' => 'Claude 3.5 Sonnet (Oct 2024)',
                    'claude-3-5-sonnet-20240620' => 'Claude 3.5 Sonnet (Jun 2024)',
                    'claude-3-5-haiku-20241022' => 'Claude 3.5 Haiku (Fast)',
                    'claude-3-opus-20240229' => 'Claude 3 Opus',
                    'claude-3-sonnet-20240229' => 'Claude 3 Sonnet',
                    'claude-3-haiku-20240307' => 'Claude 3 Haiku (Cheapest)'
                ]
            ],
            'openai' => [
                'name' => 'OpenAI',
                'has_key' => $security->hasApiKey('openai'),
                'models' => [
                    'gpt-4o' => 'GPT-4o (Recommended)',
                    'gpt-4o-mini' => 'GPT-4o Mini (Fast & Cheap)',
                    'gpt-4-turbo' => 'GPT-4 Turbo',
                    'gpt-4-turbo-preview' => 'GPT-4 Turbo Preview',
                    'gpt-4' => 'GPT-4 (Classic)',
                    'o1' => 'O1 (Reasoning)',
                    'o1-mini' => 'O1 Mini (Fast Reasoning)',
                    'o3-mini' => 'O3 Mini (Latest Reasoning)'
                ]
            ]
        ];

        echo json_encode([
            'success' => true,
            'current_provider' => $currentProvider,
            'current_model' => $currentModel,
            'providers' => $providers
        ]);
        break;

    case 'switch_provider':
        // Switch AI provider
        $provider = $input['provider'] ?? '';

        if (!in_array($provider, ['gemini', 'claude', 'openai'])) {
            echo json_encode([
                'success' => false,
                'error' => 'Invalid provider'
            ]);
            break;
        }

        // Check if provider has API key
        if (!$security->hasApiKey($provider)) {
            echo json_encode([
                'success' => false,
                'error' => 'No API key found for this provider. Please add an API key first.'
            ]);
            break;
        }

        // Update config
        $config->set('ai_provider', $provider);
        $config->save();

        echo json_encode([
            'success' => true,
            'message' => 'AI provider switched successfully'
        ]);
        break;

    case 'switch_model':
        // Switch AI model
        $model = $input['model'] ?? '';

        if (empty($model)) {
            echo json_encode([
                'success' => false,
                'error' => 'Model not specified'
            ]);
            break;
        }

        // Update config
        $config->set('ai_model', $model);
        $config->save();

        echo json_encode([
            'success' => true,
            'message' => 'AI model switched successfully'
        ]);
        break;

    case 'add_api_key':
        // Add or update API key
        $provider = $input['provider'] ?? '';
        $apiKey = $input['api_key'] ?? '';

        if (!in_array($provider, ['gemini', 'claude', 'openai'])) {
            echo json_encode([
                'success' => false,
                'error' => 'Invalid provider'
            ]);
            break;
        }

        if (empty($apiKey)) {
            echo json_encode([
                'success' => false,
                'error' => 'API key cannot be empty'
            ]);
            break;
        }

        // Validate format
        if (!$security->validateApiKeyFormat($provider, $apiKey)) {
            echo json_encode([
                'success' => false,
                'error' => 'Invalid API key format for ' . $provider
            ]);
            break;
        }

        // Store encrypted
        $result = $security->storeApiKey($provider, $apiKey);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'API key saved successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to save API key'
            ]);
        }
        break;

    case 'delete_api_key':
        // Delete API key
        $provider = $input['provider'] ?? '';

        if (!in_array($provider, ['gemini', 'claude', 'openai'])) {
            echo json_encode([
                'success' => false,
                'error' => 'Invalid provider'
            ]);
            break;
        }

        $result = $security->deleteApiKey($provider);

        // If deleting current provider, clear it from config
        if ($config->get('ai_provider') === $provider) {
            $config->set('ai_provider', null);
            $config->save();
        }

        echo json_encode([
            'success' => true,
            'message' => 'API key deleted successfully'
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}
