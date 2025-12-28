<?php
/**
 * XBuilder Security Class
 * 
 * Handles:
 * - API key encryption/decryption
 * - Admin authentication
 * - CSRF protection
 */

namespace XBuilder\Core;

class Security
{
    private string $keysPath;
    private string $configPath;
    
    public function __construct()
    {
        $this->keysPath = dirname(__DIR__) . '/storage/keys';
        $this->configPath = dirname(__DIR__) . '/storage';
        
        // Ensure directories exist
        $this->ensureDirectories();
    }
    
    private function ensureDirectories(): void
    {
        $dirs = [
            $this->keysPath,
            $this->configPath,
            dirname(__DIR__) . '/storage/conversations',
            dirname(__DIR__) . '/storage/uploads'
        ];
        
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0700, true);
            }
        }
        
        // Create .htaccess to protect storage
        $htaccess = dirname(__DIR__) . '/storage/.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Order deny,allow\nDeny from all");
        }
    }
    
    /**
     * Get or generate the server encryption key
     */
    private function getEncryptionKey(): string
    {
        $keyFile = $this->keysPath . '/.server.key';
        
        if (file_exists($keyFile)) {
            return file_get_contents($keyFile);
        }
        
        // Generate a new key using server-specific data for additional entropy
        $entropy = __DIR__ . php_uname() . microtime(true) . random_bytes(32);
        $key = hash('sha256', $entropy, true);
        
        file_put_contents($keyFile, $key);
        chmod($keyFile, 0600);
        
        return $key;
    }
    
    /**
     * Encrypt data
     */
    public function encrypt(string $data): string
    {
        $key = $this->getEncryptionKey();
        $iv = random_bytes(16);
        
        $encrypted = openssl_encrypt(
            $data,
            'AES-256-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        // Combine IV and encrypted data
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt data
     */
    public function decrypt(string $encryptedData): ?string
    {
        $key = $this->getEncryptionKey();
        $data = base64_decode($encryptedData);
        
        if ($data === false || strlen($data) < 17) {
            return null;
        }
        
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        
        $decrypted = openssl_decrypt(
            $encrypted,
            'AES-256-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        return $decrypted !== false ? $decrypted : null;
    }
    
    /**
     * Store an API key securely
     */
    public function storeApiKey(string $provider, string $apiKey): bool
    {
        $encrypted = $this->encrypt($apiKey);
        $file = $this->keysPath . '/' . $provider . '.key';
        
        $result = file_put_contents($file, $encrypted);
        if ($result !== false) {
            chmod($file, 0600);
            return true;
        }
        
        return false;
    }
    
    /**
     * Retrieve an API key
     */
    public function getApiKey(string $provider): ?string
    {
        $file = $this->keysPath . '/' . $provider . '.key';
        
        if (!file_exists($file)) {
            return null;
        }
        
        $encrypted = file_get_contents($file);
        return $this->decrypt($encrypted);
    }
    
    /**
     * Check if an API key exists
     */
    public function hasApiKey(string $provider): bool
    {
        return file_exists($this->keysPath . '/' . $provider . '.key');
    }
    
    /**
     * Delete an API key
     */
    public function deleteApiKey(string $provider): bool
    {
        $file = $this->keysPath . '/' . $provider . '.key';
        
        if (file_exists($file)) {
            // Overwrite before deleting for security
            file_put_contents($file, random_bytes(strlen(file_get_contents($file))));
            return unlink($file);
        }
        
        return false;
    }
    
    /**
     * Hash a password
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }
    
    /**
     * Verify a password
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool
    {
        return isset($_SESSION['xbuilder_authenticated']) 
            && $_SESSION['xbuilder_authenticated'] === true
            && isset($_SESSION['xbuilder_auth_time'])
            && (time() - $_SESSION['xbuilder_auth_time']) < 86400; // 24 hour session
    }
    
    /**
     * Set authenticated state
     */
    public function setAuthenticated(bool $authenticated = true): void
    {
        $_SESSION['xbuilder_authenticated'] = $authenticated;
        $_SESSION['xbuilder_auth_time'] = time();
        
        // Regenerate session ID for security
        session_regenerate_id(true);
    }
    
    /**
     * Logout
     */
    public function logout(): void
    {
        $_SESSION['xbuilder_authenticated'] = false;
        unset($_SESSION['xbuilder_auth_time']);
        session_destroy();
    }
    
    /**
     * Generate CSRF token
     */
    public function generateCsrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     */
    public function verifyCsrfToken(string $token): bool
    {
        return isset($_SESSION['csrf_token']) 
            && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Validate API key format (basic validation)
     */
    public function validateApiKeyFormat(string $provider, string $key): bool
    {
        $key = trim($key);
        
        switch ($provider) {
            case 'claude':
                // Anthropic keys start with 'sk-ant-'
                return strpos($key, 'sk-ant-') === 0 && strlen($key) > 40;
                
            case 'openai':
                // OpenAI keys start with 'sk-'
                return strpos($key, 'sk-') === 0 && strlen($key) > 20;
                
            case 'gemini':
                // Gemini keys are typically 39 characters
                return strlen($key) >= 30;
                
            default:
                return strlen($key) > 10;
        }
    }
}
