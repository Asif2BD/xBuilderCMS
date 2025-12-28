<?php
/**
 * XBuilder Security Class
 *
 * Handles:
 * - API key encryption/decryption (AES-256-CBC)
 * - Password hashing (Argon2id)
 * - CSRF protection with expiry
 * - Session management with namespacing
 *
 * Combined best practices from both implementations:
 * - Instance-based for better DI/testing
 * - Secure key deletion (overwrite before unlink)
 * - Storage directory protection (.htaccess)
 * - Session namespacing (xbuilder_*)
 * - CSRF token expiry
 */

namespace XBuilder\Core;

class Security
{
    private string $storagePath;
    private string $keysPath;
    private ?string $encryptionKey = null;

    private const CIPHER = 'AES-256-CBC';
    private const SESSION_PREFIX = 'xbuilder_';
    private const SESSION_LIFETIME = 86400; // 24 hours
    private const CSRF_LIFETIME = 3600; // 1 hour

    public function __construct()
    {
        $this->storagePath = dirname(__DIR__) . '/storage';
        $this->keysPath = $this->storagePath . '/keys';
        $this->ensureDirectories();
    }

    /**
     * Ensure storage directories exist with proper permissions
     */
    private function ensureDirectories(): void
    {
        $dirs = [
            $this->storagePath,
            $this->keysPath,
            $this->storagePath . '/conversations',
            $this->storagePath . '/uploads'
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0700, true);
            }
        }

        // Create .htaccess to protect storage directory (defense in depth)
        $htaccessPath = $this->storagePath . '/.htaccess';
        if (!file_exists($htaccessPath)) {
            file_put_contents($htaccessPath, "Order deny,allow\nDeny from all\n");
            chmod($htaccessPath, 0600);
        }

        // Create index.php to prevent directory listing
        $indexPath = $this->storagePath . '/index.php';
        if (!file_exists($indexPath)) {
            file_put_contents($indexPath, "<?php\n// Silence is golden\n");
            chmod($indexPath, 0600);
        }
    }

    /**
     * Get or generate the server encryption key
     */
    private function getEncryptionKey(): string
    {
        if ($this->encryptionKey !== null) {
            return $this->encryptionKey;
        }

        $keyFile = $this->keysPath . '/.server.key';

        if (file_exists($keyFile)) {
            $this->encryptionKey = file_get_contents($keyFile);
            return $this->encryptionKey;
        }

        // Generate new key with server-specific entropy
        $entropy = __DIR__ . php_uname() . microtime(true) . random_bytes(32);
        $this->encryptionKey = hash('sha256', $entropy, true);

        file_put_contents($keyFile, $this->encryptionKey);
        chmod($keyFile, 0600);

        return $this->encryptionKey;
    }

    /**
     * Encrypt data using AES-256-CBC
     */
    public function encrypt(string $data): string
    {
        $key = $this->getEncryptionKey();
        $iv = random_bytes(16);

        $encrypted = openssl_encrypt(
            $data,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($encrypted === false) {
            throw new \RuntimeException('Encryption failed');
        }

        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt data using AES-256-CBC
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
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        return $decrypted !== false ? $decrypted : null;
    }

    /**
     * Store an API key securely (encrypted)
     */
    public function storeApiKey(string $provider, string $apiKey): bool
    {
        $encrypted = $this->encrypt($apiKey);
        $file = $this->keysPath . '/' . $provider . '.key';

        $result = file_put_contents($file, $encrypted, LOCK_EX);
        if ($result !== false) {
            chmod($file, 0600);
            return true;
        }

        return false;
    }

    /**
     * Retrieve an API key (decrypted)
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
     * Delete an API key securely (overwrite before delete)
     */
    public function deleteApiKey(string $provider): bool
    {
        $file = $this->keysPath . '/' . $provider . '.key';

        if (file_exists($file)) {
            // Overwrite with random bytes before deleting (secure deletion)
            $size = filesize($file);
            file_put_contents($file, random_bytes($size));
            return unlink($file);
        }

        return true;
    }

    /**
     * Hash a password using Argon2id
     */
    public function hashPassword(string $password): string
    {
        $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;

        $options = [];
        if ($algo === PASSWORD_ARGON2ID) {
            $options = [
                'memory_cost' => 65536,
                'time_cost' => 4,
                'threads' => 3
            ];
        }

        return password_hash($password, $algo, $options);
    }

    /**
     * Verify a password against a hash
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Ensure session is started
     */
    private function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool
    {
        $this->ensureSession();

        $authKey = self::SESSION_PREFIX . 'authenticated';
        $timeKey = self::SESSION_PREFIX . 'auth_time';

        if (!isset($_SESSION[$authKey]) || $_SESSION[$authKey] !== true) {
            return false;
        }

        if (!isset($_SESSION[$timeKey])) {
            return false;
        }

        // Check session expiry
        if ((time() - $_SESSION[$timeKey]) > self::SESSION_LIFETIME) {
            $this->logout();
            return false;
        }

        return true;
    }

    /**
     * Set authenticated state
     */
    public function setAuthenticated(bool $authenticated = true): void
    {
        $this->ensureSession();

        $_SESSION[self::SESSION_PREFIX . 'authenticated'] = $authenticated;
        $_SESSION[self::SESSION_PREFIX . 'auth_time'] = time();

        // Regenerate session ID for security
        session_regenerate_id(true);
    }

    /**
     * Authenticate with password
     */
    public function authenticate(string $password, string $hash): bool
    {
        if (!$this->verifyPassword($password, $hash)) {
            // Rate limiting: delay on failed attempt
            usleep(500000); // 0.5 second delay
            return false;
        }

        $this->setAuthenticated(true);
        return true;
    }

    /**
     * Logout and destroy session
     */
    public function logout(): void
    {
        $this->ensureSession();

        // Clear all xbuilder session keys
        foreach ($_SESSION as $key => $value) {
            if (strpos($key, self::SESSION_PREFIX) === 0) {
                unset($_SESSION[$key]);
            }
        }

        // Destroy session completely
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Require authentication or redirect to login
     */
    public function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            header('Location: /xbuilder/login');
            exit;
        }
    }

    /**
     * Generate CSRF token with expiry
     */
    public function generateCsrfToken(): string
    {
        $this->ensureSession();

        $tokenKey = self::SESSION_PREFIX . 'csrf_token';
        $timeKey = self::SESSION_PREFIX . 'csrf_time';

        // Generate new token if none exists or expired
        if (!isset($_SESSION[$tokenKey]) ||
            !isset($_SESSION[$timeKey]) ||
            (time() - $_SESSION[$timeKey]) > self::CSRF_LIFETIME) {

            $_SESSION[$tokenKey] = bin2hex(random_bytes(32));
            $_SESSION[$timeKey] = time();
        }

        return $_SESSION[$tokenKey];
    }

    /**
     * Verify CSRF token
     */
    public function verifyCsrfToken(string $token): bool
    {
        $this->ensureSession();

        $tokenKey = self::SESSION_PREFIX . 'csrf_token';
        $timeKey = self::SESSION_PREFIX . 'csrf_time';

        if (!isset($_SESSION[$tokenKey]) || !isset($_SESSION[$timeKey])) {
            return false;
        }

        // Check token expiry
        if ((time() - $_SESSION[$timeKey]) > self::CSRF_LIFETIME) {
            unset($_SESSION[$tokenKey], $_SESSION[$timeKey]);
            return false;
        }

        return hash_equals($_SESSION[$tokenKey], $token);
    }

    /**
     * Validate API key format
     */
    public function validateApiKeyFormat(string $provider, string $key): bool
    {
        $key = trim($key);

        switch ($provider) {
            case 'claude':
                return strpos($key, 'sk-ant-') === 0 && strlen($key) > 40;

            case 'openai':
                return strpos($key, 'sk-') === 0 && strlen($key) > 20;

            case 'gemini':
                return preg_match('/^[a-zA-Z0-9_-]{30,50}$/', $key) === 1;

            default:
                return strlen($key) > 10;
        }
    }

    /**
     * Sanitize output for HTML
     */
    public function sanitize(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Validate file upload
     */
    public function validateUpload(array $file, array $allowedTypes, int $maxSize = 10485760): array
    {
        $errors = [];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds server limit',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds form limit',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file',
                UPLOAD_ERR_EXTENSION => 'Upload blocked by extension',
            ];
            $errors[] = $uploadErrors[$file['error']] ?? 'Unknown upload error';
            return $errors;
        }

        if ($file['size'] > $maxSize) {
            $errors[] = 'File exceeds maximum size of ' . round($maxSize / 1048576, 1) . 'MB';
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedTypes)) {
            $errors[] = 'File type not allowed. Allowed: ' . implode(', ', $allowedTypes);
        }

        return $errors;
    }

    /**
     * Get storage path
     */
    public function getStoragePath(): string
    {
        return $this->storagePath;
    }
}
