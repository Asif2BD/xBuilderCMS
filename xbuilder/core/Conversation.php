<?php
/**
 * XBuilder Conversation Class
 *
 * Manages conversation history and context.
 * Stores conversations as JSON files in the storage directory.
 *
 * Combined best practices:
 * - Session + file-based conversation tracking
 * - Document storage with metadata
 * - Conversation archiving for history
 * - Summary and export methods
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
     * Get or create current conversation ID (session + file based)
     */
    private function getCurrentConversationId(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Use session-based conversation tracking
        if (isset($_SESSION['xbuilder_conversation_id'])) {
            return $_SESSION['xbuilder_conversation_id'];
        }

        // Check for existing active conversation
        $activeFile = $this->storagePath . '/active.json';
        if (file_exists($activeFile)) {
            $active = json_decode(file_get_contents($activeFile), true);
            if (isset($active['id'])) {
                $_SESSION['xbuilder_conversation_id'] = $active['id'];
                return $active['id'];
            }
        }

        // Create new conversation
        $id = 'conv_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4));
        $_SESSION['xbuilder_conversation_id'] = $id;

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
            $content = file_get_contents($file);
            $this->data = json_decode($content, true) ?? [];

            if (!empty($this->data)) {
                return;
            }
        }

        // Initialize new conversation
        $this->data = [
            'id' => $this->conversationId,
            'created_at' => date('c'),
            'updated_at' => date('c'),
            'messages' => [],
            'document' => null,
            'generated_html' => null,
            'context' => []
        ];
    }

    /**
     * Save conversation data
     */
    public function save(): bool
    {
        $this->data['updated_at'] = date('c');

        $result = file_put_contents(
            $this->getFilePath(),
            json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            LOCK_EX
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
     * Store uploaded document content with metadata
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
     * Get document metadata
     */
    public function getDocumentInfo(): ?array
    {
        return $this->data['document'] ?? null;
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
    public function getContext(?string $key = null)
    {
        if ($key === null) {
            return $this->data['context'] ?? [];
        }
        return $this->data['context'][$key] ?? null;
    }

    /**
     * Get conversation ID
     */
    public function getId(): string
    {
        return $this->conversationId;
    }

    /**
     * Check if conversation has messages
     */
    public function hasMessages(): bool
    {
        return !empty($this->data['messages']);
    }

    /**
     * Get the last message
     */
    public function getLastMessage(): ?array
    {
        $messages = $this->data['messages'] ?? [];
        return !empty($messages) ? end($messages) : null;
    }

    /**
     * Clear current conversation and start fresh
     */
    public function clear(): void
    {
        // Archive current conversation if it has messages
        if ($this->hasMessages()) {
            $this->archive();
        }

        // Generate new ID
        $newId = 'conv_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4));

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION['xbuilder_conversation_id'] = $newId;

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
            'document' => null,
            'generated_html' => null,
            'context' => []
        ];
        $this->save();
    }

    /**
     * Start a new conversation (alias for clear)
     */
    public function startNew(): string
    {
        $this->clear();
        return $this->conversationId;
    }

    /**
     * Archive the current conversation
     */
    public function archive(): bool
    {
        if (!$this->hasMessages()) {
            return false;
        }

        // Conversation is already saved with its ID, just mark it as archived
        $this->data['archived_at'] = date('c');
        return $this->save();
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
     * List all archived conversations
     */
    public static function listArchived(): array
    {
        $dir = dirname(__DIR__) . '/storage/conversations';

        if (!is_dir($dir)) {
            return [];
        }

        $files = glob($dir . '/conv_*.json');
        $conversations = [];

        foreach ($files as $file) {
            $content = file_get_contents($file);
            $data = json_decode($content, true);

            if ($data && !empty($data['messages'])) {
                $conversations[] = [
                    'id' => $data['id'] ?? basename($file, '.json'),
                    'created_at' => $data['created_at'] ?? null,
                    'updated_at' => $data['updated_at'] ?? null,
                    'message_count' => count($data['messages'] ?? []),
                    'has_site' => !empty($data['generated_html'])
                ];
            }
        }

        // Sort by updated_at descending
        usort($conversations, function ($a, $b) {
            return strtotime($b['updated_at'] ?? '0') - strtotime($a['updated_at'] ?? '0');
        });

        return $conversations;
    }

    /**
     * Load an archived conversation
     */
    public static function loadArchived(string $id): ?self
    {
        $path = dirname(__DIR__) . '/storage/conversations/' . $id . '.json';

        if (!file_exists($path)) {
            return null;
        }

        return new self($id);
    }

    /**
     * Delete an archived conversation
     */
    public static function deleteArchived(string $id): bool
    {
        $path = dirname(__DIR__) . '/storage/conversations/' . $id . '.json';

        if (file_exists($path)) {
            return unlink($path);
        }

        return false;
    }

    /**
     * Export conversation as array
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
