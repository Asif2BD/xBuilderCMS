<?php
/**
 * XBuilder Chat API
 *
 * Handles conversation with AI for website generation.
 *
 * Variables available from router:
 * - $GLOBALS['xbuilder_config']: Config instance
 * - $GLOBALS['xbuilder_security']: Security instance
 */

use XBuilder\Core\AI;
use XBuilder\Core\Conversation;
use XBuilder\Core\Generator;

header('Content-Type: application/json');

$config = $GLOBALS['xbuilder_config'];
$security = $GLOBALS['xbuilder_security'];

// Check authentication
if (!$security->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Verify CSRF token
$csrfToken = $_POST['csrf_token'] ?? '';
if (!$security->verifyCsrfToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid security token']);
    exit;
}

// Get message
$message = trim($_POST['message'] ?? '');

if (empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Message is required']);
    exit;
}

try {
    // Get AI provider
    $provider = $config->getAiProvider();
    if (!$provider) {
        throw new \RuntimeException('AI provider not configured');
    }

    // Initialize conversation and AI
    $conversation = new Conversation();
    $ai = new AI($provider, $security);
    $generator = new Generator();

    // Add user message to conversation
    $conversation->addMessage('user', $message);

    // Get document content if any (for first message context)
    $documentContent = $conversation->getDocumentContent();

    // Get AI response
    $messages = $conversation->getMessagesForAI();
    $response = $ai->chat($messages, $documentContent);

    if (!$response) {
        throw new \RuntimeException('Failed to get AI response');
    }

    // Add AI response to conversation
    $conversation->addMessage('assistant', $response['content']);

    // Check for generated HTML
    $hasHtml = !empty($response['html']);

    if ($hasHtml) {
        // Validate HTML
        $errors = $generator->validateHtml($response['html']);
        if (!empty($errors)) {
            // Log validation issues but continue
            error_log('XBuilder HTML validation warnings: ' . implode(', ', $errors));
        }

        // Save to preview and conversation
        $generator->savePreview($response['html']);
        $conversation->setGeneratedHtml($response['html']);

        // Mark site as generated in config
        $config->setSiteGenerated(true);
    }

    echo json_encode([
        'success' => true,
        'content' => $response['content'],
        'hasHtml' => $hasHtml,
        'html' => $response['html'] ?? null
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'success' => false
    ]);
}
