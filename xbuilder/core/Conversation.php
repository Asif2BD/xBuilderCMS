<?php
/**
 * XBuilder Conversation Class
 * 
 * Manages conversation history and context
 */

namespace XBuilder\Core;

class Conversation
{
    private string $storagePath;
    private string $conversationId;
    private array $data;
    
    public function __construct(?string $conversationId = null)
    {
        $this->storagePath = dirname(__DIR__) . '/storage/conversations';
        
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0700, true);
        }
        
        $this->conversationId = $conversationId ?? $this->getCurrentConversationId();
        $this->load();
    }
    
    /**
     * Get or create current conversation ID
     */
    private function getCurrentConversationId(): string
    {
        // Use session-based conversation tracking
        if (isset($_SESSION['conversation_id'])) {
            return $_SESSION['conversation_id'];
        }
        
        // Check for existing active conversation
        $activeFile = $this->storagePath . '/active.json';
        if (file_exists($activeFile)) {
            $active = json_decode(file_get_contents($activeFile), true);
            if (isset($active['id'])) {
                $_SESSION['conversation_id'] = $active['id'];
                return $active['id'];
            }
        }
        
        // Create new conversation
        $id = 'conv_' . bin2hex(random_bytes(8));
        $_SESSION['conversation_id'] = $id;
        
        // Save as active conversation
        file_put_contents($activeFile, json_encode(['id' => $id, 'created' => date('c')]));
        
        return $id;
    }
    
    /**
     * Get the file path for this conversation
     */
    private function getFilePath(): string
    {
        return $this->storagePath . '/' . $this->conversationId . '.json';
    }
    
    /**
     * Load conversation data
     */
    private function load(): void
    {
        $file = $this->getFilePath();
        
        if (file_exists($file)) {
            $this->data = json_decode(file_get_contents($file), true) ?? [];
        } else {
            $this->data = [
                'id' => $this->conversationId,
                'created_at' => date('c'),
                'updated_at' => date('c'),
                'messages' => [],
                'context' => [],
                'generated_html' => null
            ];
        }
    }
    
    /**
     * Save conversation data
     */
    private function save(): bool
    {
        $this->data['updated_at'] = date('c');
        
        $result = file_put_contents(
            $this->getFilePath(),
            json_encode($this->data, JSON_PRETTY_PRINT)
        );
        
        return $result !== false;
    }
    
    /**
     * Add a message to the conversation
     */
    public function addMessage(string $role, string $content): void
    {
        $this->data['messages'][] = [
            'role' => $role,
            'content' => $content,
            'timestamp' => date('c')
        ];
        
        $this->save();
    }
    
    /**
     * Get all messages
     */
    public function getMessages(): array
    {
        return $this->data['messages'] ?? [];
    }
    
    /**
     * Get messages formatted for AI API
     */
    public function getMessagesForAI(): array
    {
        return array_map(function($msg) {
            return [
                'role' => $msg['role'],
                'content' => $msg['content']
            ];
        }, $this->data['messages'] ?? []);
    }
    
    /**
     * Set context data
     */
    public function setContext(string $key, $value): void
    {
        $this->data['context'][$key] = $value;
        $this->save();
    }
    
    /**
     * Get context data
     */
    public function getContext(string $key = null)
    {
        if ($key === null) {
            return $this->data['context'] ?? [];
        }
        return $this->data['context'][$key] ?? null;
    }
    
    /**
     * Store uploaded document content
     */
    public function setDocumentContent(string $content, string $filename): void
    {
        $this->data['document'] = [
            'filename' => $filename,
            'content' => $content,
            'uploaded_at' => date('c')
        ];
        $this->save();
    }
    
    /**
     * Get uploaded document content
     */
    public function getDocumentContent(): ?string
    {
        return $this->data['document']['content'] ?? null;
    }
    
    /**
     * Store generated HTML
     */
    public function setGeneratedHtml(string $html): void
    {
        $this->data['generated_html'] = $html;
        $this->data['html_generated_at'] = date('c');
        $this->save();
    }
    
    /**
     * Get generated HTML
     */
    public function getGeneratedHtml(): ?string
    {
        return $this->data['generated_html'] ?? null;
    }
    
    /**
     * Clear conversation and start fresh
     */
    public function clear(): void
    {
        $this->data = [
            'id' => $this->conversationId,
            'created_at' => date('c'),
            'updated_at' => date('c'),
            'messages' => [],
            'context' => [],
            'generated_html' => null
        ];
        $this->save();
    }
    
    /**
     * Get conversation summary (for display)
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->conversationId,
            'message_count' => count($this->data['messages'] ?? []),
            'has_document' => isset($this->data['document']),
            'has_generated_html' => !empty($this->data['generated_html']),
            'created_at' => $this->data['created_at'] ?? null,
            'updated_at' => $this->data['updated_at'] ?? null
        ];
    }
    
    /**
     * Start a new conversation
     */
    public function startNew(): string
    {
        // Generate new ID
        $newId = 'conv_' . bin2hex(random_bytes(8));
        
        // Update session
        $_SESSION['conversation_id'] = $newId;
        
        // Update active conversation file
        $activeFile = $this->storagePath . '/active.json';
        file_put_contents($activeFile, json_encode(['id' => $newId, 'created' => date('c')]));
        
        // Reset this instance
        $this->conversationId = $newId;
        $this->data = [
            'id' => $newId,
            'created_at' => date('c'),
            'updated_at' => date('c'),
            'messages' => [],
            'context' => [],
            'generated_html' => null
        ];
        $this->save();
        
        return $newId;
    }
}
