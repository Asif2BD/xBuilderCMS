<?php
/**
 * XBuilder Chat API
 * 
 * Handles conversation with AI providers
 */

header('Content-Type: application/json');

require_once dirname(__DIR__) . '/core/Security.php';
require_once dirname(__DIR__) . '/core/Config.php';
require_once dirname(__DIR__) . '/core/AI.php';
require_once dirname(__DIR__) . '/core/Conversation.php';
require_once dirname(__DIR__) . '/core/Generator.php';

use XBuilder\Core\Security;
use XBuilder\Core\Config;
use XBuilder\Core\AI;
use XBuilder\Core\Conversation;
use XBuilder\Core\Generator;

$security = new Security();
$config = new Config();
$conversation = new Conversation();

// Handle GET requests (load conversation)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? 'load';
    
    if ($action === 'load') {
        echo json_encode([
            'success' => true,
            'messages' => $conversation->getMessages(),
            'generated_html' => $conversation->getGeneratedHtml(),
            'context' => $conversation->getContext()
        ]);
        exit;
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check for clear action
    if (isset($_GET['action']) && $_GET['action'] === 'clear') {
        $conversation->clear();
        echo json_encode(['success' => true]);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['message'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Message required']);
        exit;
    }
    
    $message = trim($input['message']);
    $documentContent = $input['document'] ?? null;

    if (empty($message)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
        exit;
    }

    // Log document content for debugging
    if ($documentContent) {
        error_log("[XBuilder Chat] Received document: " . strlen($documentContent) . " chars");
    } else {
        error_log("[XBuilder Chat] No document content");
    }

    // Store user message
    $conversation->addMessage('user', $message);

    // If document content provided, store it
    if ($documentContent) {
        $conversation->setDocumentContent($documentContent, 'uploaded_document');
    }
    
    // Get AI provider
    $provider = $config->getAiProvider();
    if (!$provider) {
        echo json_encode(['success' => false, 'error' => 'AI provider not configured']);
        exit;
    }

    // Get custom model if set
    $customModel = $config->get('ai_model');

    // Initialize AI with optional custom model
    $ai = new AI($provider, null, $customModel);
    
    // Get conversation history for context
    $history = $conversation->getMessagesForAI();
    
    // Send to AI
    $result = $ai->chat($history, $documentContent);
    
    if (!$result['success']) {
        echo json_encode([
            'success' => false,
            'error' => $result['error'] ?? 'AI request failed'
        ]);
        exit;
    }
    
    // Store AI response
    $conversation->addMessage('assistant', $result['message']);
    
    // If HTML was generated, save it
    if (!empty($result['html'])) {
        $conversation->setGeneratedHtml($result['html']);
        
        // Save as preview
        $generator = new Generator();
        $generator->savePreview($result['html']);
    }
    
    echo json_encode([
        'success' => true,
        'message' => $result['message'],
        'html' => $result['html'] ?? null
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
