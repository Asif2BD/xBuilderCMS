<?php
/**
 * XBuilder Chat API
 *
 * Handles AI conversation:
 * - Receives user messages
 * - Sends to AI provider
 * - Parses response for HTML
 * - Stores conversation history
 */

use XBuilder\Core\Config;
use XBuilder\Core\Security;
use XBuilder\Core\AI;
use XBuilder\Core\Conversation;
use XBuilder\Core\Generator;

header('Content-Type: application/json');

// Require authentication
if (!Security::isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Verify CSRF token
$csrfToken = $_POST['csrf_token'] ?? '';
if (!Security::verifyCsrfToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid security token']);
    exit;
}

// Get message
$message = $_POST['message'] ?? '';
$message = trim($message);

if (empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Message is required']);
    exit;
}

try {
    // Load conversation
    $conversation = new Conversation();

    // Add user message
    $conversation->addMessage('user', $message);

    // Get document content if any
    $documentContent = $conversation->getDocumentContent();

    // Initialize AI
    $ai = new AI();

    // Get messages for AI (includes history)
    $messages = $conversation->getMessagesForAI();

    // Send to AI
    $response = $ai->chat($messages, $documentContent);

    // Add assistant response to conversation
    $conversation->addMessage('assistant', $response['content']);

    // Check for generated HTML
    $hasHtml = !empty($response['html']);

    if ($hasHtml) {
        // Validate HTML
        $errors = Generator::validateHtml($response['html']);

        if (empty($errors)) {
            // Save to conversation and preview
            $conversation->setGeneratedHtml($response['html']);
            Generator::savePreview($response['html']);
        } else {
            // HTML has issues, but still save it
            $conversation->setGeneratedHtml($response['html']);
            Generator::savePreview($response['html']);
        }
    }

    echo json_encode([
        'success' => true,
        'content' => $response['content'],
        'hasHtml' => $hasHtml,
        'html' => $response['html'] ?? null,
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'success' => false
    ]);
}
