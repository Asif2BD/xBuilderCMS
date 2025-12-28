<?php
/**
 * XBuilder Conversation Class
 *
 * Manages chat history and conversation state.
 * Stores conversations as JSON files in the storage directory.
 */

namespace XBuilder\Core;

class Conversation
{
    private const CONVERSATION_DIR = 'conversations';
    private const CURRENT_FILE = 'current.json';

    private string $conversationPath;
    private array $data = [];

    public function __construct(?string $conversationId = null)
    {
        $dir = XBUILDER_STORAGE . '/' . self::CONVERSATION_DIR;

        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        // Use provided ID or load the current conversation
        if ($conversationId) {
            $this->conversationPath = $dir . '/' . $conversationId . '.json';
        } else {
            $this->conversationPath = $dir . '/' . self::CURRENT_FILE;
        }

        $this->load();
    }

    /**
     * Load conversation from file
     */
    private function load(): void
    {
        if (file_exists($this->conversationPath)) {
            $content = file_get_contents($this->conversationPath);
            $data = json_decode($content, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $this->data = $data;
                return;
            }
        }

        // Initialize new conversation
        $this->data = [
            'id' => $this->generateId(),
            'created_at' => date('c'),
            'updated_at' => date('c'),
            'messages' => [],
            'document_content' => null,
            'generated_html' => null,
            'metadata' => [],
        ];
    }

    /**
     * Save conversation to file
     */
    public function save(): bool
    {
        $this->data['updated_at'] = date('c');

        $json = json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            return false;
        }

        $result = file_put_contents($this->conversationPath, $json, LOCK_EX);
        return $result !== false;
    }

    /**
     * Generate a unique conversation ID
     */
    private function generateId(): string
    {
        return date('Ymd_His') . '_' . bin2hex(random_bytes(4));
    }

    /**
     * Add a message to the conversation
     */
    public function addMessage(string $role, string $content): void
    {
        $this->data['messages'][] = [
            'role' => $role,
            'content' => $content,
            'timestamp' => date('c'),
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
        return array_map(function ($msg) {
            return [
                'role' => $msg['role'],
                'content' => $msg['content'],
            ];
        }, $this->data['messages'] ?? []);
    }

    /**
     * Set document content (from uploaded CV/resume)
     */
    public function setDocumentContent(?string $content): void
    {
        $this->data['document_content'] = $content;
        $this->save();
    }

    /**
     * Get document content
     */
    public function getDocumentContent(): ?string
    {
        return $this->data['document_content'] ?? null;
    }

    /**
     * Set the generated HTML
     */
    public function setGeneratedHtml(?string $html): void
    {
        $this->data['generated_html'] = $html;
        $this->save();
    }

    /**
     * Get the generated HTML
     */
    public function getGeneratedHtml(): ?string
    {
        return $this->data['generated_html'] ?? null;
    }

    /**
     * Set metadata
     */
    public function setMetadata(string $key, $value): void
    {
        $this->data['metadata'][$key] = $value;
        $this->save();
    }

    /**
     * Get metadata
     */
    public function getMetadata(string $key, $default = null)
    {
        return $this->data['metadata'][$key] ?? $default;
    }

    /**
     * Get conversation ID
     */
    public function getId(): string
    {
        return $this->data['id'];
    }

    /**
     * Check if conversation has any messages
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
     * Clear the current conversation and start fresh
     */
    public function clear(): void
    {
        // Archive the current conversation if it has messages
        if ($this->hasMessages()) {
            $this->archive();
        }

        // Reset data
        $this->data = [
            'id' => $this->generateId(),
            'created_at' => date('c'),
            'updated_at' => date('c'),
            'messages' => [],
            'document_content' => null,
            'generated_html' => null,
            'metadata' => [],
        ];
        $this->save();
    }

    /**
     * Archive the current conversation
     */
    public function archive(): bool
    {
        if (!$this->hasMessages()) {
            return false;
        }

        $archivePath = XBUILDER_STORAGE . '/' . self::CONVERSATION_DIR . '/' . $this->data['id'] . '.json';
        $json = json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return file_put_contents($archivePath, $json, LOCK_EX) !== false;
    }

    /**
     * List all archived conversations
     */
    public static function listArchived(): array
    {
        $dir = XBUILDER_STORAGE . '/' . self::CONVERSATION_DIR;

        if (!is_dir($dir)) {
            return [];
        }

        $files = glob($dir . '/*.json');
        $conversations = [];

        foreach ($files as $file) {
            $filename = basename($file);

            // Skip the current conversation file
            if ($filename === self::CURRENT_FILE) {
                continue;
            }

            $content = file_get_contents($file);
            $data = json_decode($content, true);

            if ($data) {
                $conversations[] = [
                    'id' => $data['id'],
                    'created_at' => $data['created_at'],
                    'updated_at' => $data['updated_at'],
                    'message_count' => count($data['messages'] ?? []),
                    'has_site' => !empty($data['generated_html']),
                ];
            }
        }

        // Sort by updated_at descending
        usort($conversations, function ($a, $b) {
            return strtotime($b['updated_at']) - strtotime($a['updated_at']);
        });

        return $conversations;
    }

    /**
     * Load an archived conversation
     */
    public static function loadArchived(string $id): ?self
    {
        $path = XBUILDER_STORAGE . '/' . self::CONVERSATION_DIR . '/' . $id . '.json';

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
        $path = XBUILDER_STORAGE . '/' . self::CONVERSATION_DIR . '/' . $id . '.json';

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
