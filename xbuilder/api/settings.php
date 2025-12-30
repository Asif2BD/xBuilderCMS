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
                    'gemini-3-pro' => 'Gemini 3 Pro (Latest - Nov 2025)',
                    'gemini-3-flash' => 'Gemini 3 Flash (Latest)',
                    'gemini-3-deep-think' => 'Gemini 3 Deep Think',
                    'gemini-2.5-pro' => 'Gemini 2.5 Pro',
                    'gemini-2.5-flash' => 'Gemini 2.5 Flash',
                    'gemini-2.5-flash-lite' => 'Gemini 2.5 Flash-Lite (Speed)',
                    'gemini-2.0-flash' => 'Gemini 2.0 Flash (Free)',
                    'gemini-2.0-flash-lite' => 'Gemini 2.0 Flash-Lite (Free)',
                    'gemini-2.0-flash-thinking-exp' => 'Gemini 2.0 Flash Thinking (Free)',
                    'gemini-2.0-pro' => 'Gemini 2.0 Pro',
                    'gemini-1.5-flash' => 'Gemini 1.5 Flash (Free)',
                    'gemini-1.5-pro' => 'Gemini 1.5 Pro'
                ]
            ],
            'claude' => [
                'name' => 'Anthropic Claude',
                'has_key' => $security->hasApiKey('claude'),
                'models' => [
                    'claude-sonnet-4-5-20250929' => 'Claude Sonnet 4.5 (Recommended)',
                    'claude-opus-4-5-20251101' => 'Claude Opus 4.5 (Most Intelligent)',
                    'claude-haiku-4-5' => 'Claude Haiku 4.5 (Fastest)',
                    'claude-3-5-sonnet-20241022' => 'Claude 3.5 Sonnet (Oct 2024)',
                    'claude-3-5-sonnet-20240620' => 'Claude 3.5 Sonnet (Jun 2024)',
                    'claude-3-5-haiku-20241022' => 'Claude 3.5 Haiku',
                    'claude-3-opus-20240229' => 'Claude 3 Opus',
                    'claude-3-sonnet-20240229' => 'Claude 3 Sonnet',
                    'claude-3-haiku-20240307' => 'Claude 3 Haiku'
                ]
            ],
            'openai' => [
                'name' => 'OpenAI',
                'has_key' => $security->hasApiKey('openai'),
                'models' => [
                    // GPT-5.2 (Latest - December 2025)
                    'gpt-5.2' => 'GPT-5.2 (Latest Flagship)',
                    'gpt-5.2-2025-12-11' => 'GPT-5.2 (Dec 2025)',
                    'gpt-5.2-chat-latest' => 'GPT-5.2 Chat Instant',
                    'gpt-5.2-pro' => 'GPT-5.2 Pro',
                    // GPT-5.1 (November 2025)
                    'gpt-5.1' => 'GPT-5.1',
                    'gpt-5.1-2025-11-13' => 'GPT-5.1 (Nov 2025)',
                    'gpt-5.1-chat-latest' => 'GPT-5.1 Chat Instant',
                    'gpt-5.1-codex' => 'GPT-5.1 Codex (Coding)',
                    'gpt-5.1-codex-mini' => 'GPT-5.1 Codex Mini',
                    // GPT-5 (August 2025)
                    'gpt-5' => 'GPT-5',
                    'gpt-5-2025-08-07' => 'GPT-5 (Aug 2025)',
                    'gpt-5-mini' => 'GPT-5 Mini',
                    'gpt-5-mini-2025-08-07' => 'GPT-5 Mini (Aug 2025)',
                    'gpt-5-nano' => 'GPT-5 Nano',
                    'gpt-5-nano-2025-08-07' => 'GPT-5 Nano (Aug 2025)',
                    'gpt-5-chat-latest' => 'GPT-5 Chat',
                    // O-series Reasoning
                    'o3' => 'O3 (Reasoning)',
                    'o3-mini' => 'O3 Mini',
                    'o3-pro' => 'O3 Pro',
                    'o4-mini' => 'O4 Mini',
                    'o1' => 'O1',
                    // GPT-4.1 family
                    'gpt-4.1' => 'GPT-4.1 (1M context)',
                    'gpt-4.1-mini' => 'GPT-4.1 Mini',
                    'gpt-4.1-nano' => 'GPT-4.1 Nano',
                    // GPT-4 family
                    'gpt-4o' => 'GPT-4o',
                    'gpt-4o-mini' => 'GPT-4o Mini',
                    'gpt-4-turbo' => 'GPT-4 Turbo',
                    'gpt-4' => 'GPT-4'
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
