<?php
/**
 * XBuilder Security Class
 *
 * Handles encryption, password hashing, CSRF protection, and authentication.
 * Uses AES-256-CBC for encryption and Argon2id for password hashing.
 */

namespace XBuilder\Core;

class Security
{
    private const CIPHER = 'aes-256-cbc';
    private const KEY_FILE = 'encryption.key';

    private static ?string $encryptionKey = null;

    /**
     * Get or generate the server-specific encryption key
     */
    public static function getEncryptionKey(): string
    {
        if (self::$encryptionKey !== null) {
            return self::$encryptionKey;
        }

        $keyPath = XBUILDER_STORAGE . '/keys/' . self::KEY_FILE;

        if (file_exists($keyPath)) {
            self::$encryptionKey = file_get_contents($keyPath);
        } else {
            // Generate a new key using server-specific entropy
            $entropy = __DIR__ . php_uname() . microtime(true) . random_bytes(32);
            self::$encryptionKey = hash('sha256', $entropy, true);

            // Ensure directory exists
            $dir = dirname($keyPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0700, true);
            }

            // Save the key securely
            file_put_contents($keyPath, self::$encryptionKey);
            chmod($keyPath, 0600);
        }

        return self::$encryptionKey;
    }

    /**
     * Encrypt data using AES-256-CBC
     */
    public static function encrypt(string $data): string
    {
        $key = self::getEncryptionKey();
        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        $iv = random_bytes($ivLength);

        $encrypted = openssl_encrypt($data, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            throw new \RuntimeException('Encryption failed');
        }

        // Prepend IV to encrypted data and base64 encode
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt data using AES-256-CBC
     */
    public static function decrypt(string $encryptedData): string
    {
        $key = self::getEncryptionKey();
        $data = base64_decode($encryptedData);

        if ($data === false) {
            throw new \RuntimeException('Invalid encrypted data');
        }

        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);

        $decrypted = openssl_decrypt($encrypted, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            throw new \RuntimeException('Decryption failed');
        }

        return $decrypted;
    }

    /**
     * Hash a password using Argon2id
     */
    public static function hashPassword(string $password): string
    {
        // Use Argon2id if available, fallback to bcrypt
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
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Generate a CSRF token
     */
    public static function generateCsrfToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();

        return $token;
    }

    /**
     * Verify a CSRF token
     */
    public static function verifyCsrfToken(string $token): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }

        // Token expires after 1 hour
        if (time() - $_SESSION['csrf_token_time'] > 3600) {
            unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!isset($_SESSION['authenticated']) || !isset($_SESSION['auth_time'])) {
            return false;
        }

        // Session expires after 24 hours
        if (time() - $_SESSION['auth_time'] > 86400) {
            self::logout();
            return false;
        }

        return $_SESSION['authenticated'] === true;
    }

    /**
     * Authenticate a user
     */
    public static function authenticate(string $password): bool
    {
        $config = Config::load();

        if (!isset($config['password_hash'])) {
            return false;
        }

        if (!self::verifyPassword($password, $config['password_hash'])) {
            // Rate limiting: delay on failed attempt
            usleep(500000); // 0.5 second delay
            return false;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Regenerate session ID on login
        session_regenerate_id(true);

        $_SESSION['authenticated'] = true;
        $_SESSION['auth_time'] = time();

        return true;
    }

    /**
     * Log out the current user
     */
    public static function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION = [];

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
    public static function requireAuth(): void
    {
        if (!self::isAuthenticated()) {
            header('Location: /xbuilder/login');
            exit;
        }
    }

    /**
     * Validate API key format
     */
    public static function validateApiKeyFormat(string $provider, string $key): bool
    {
        $key = trim($key);

        switch ($provider) {
            case 'claude':
                // Claude keys start with sk-ant-
                return preg_match('/^sk-ant-[a-zA-Z0-9_-]+$/', $key) === 1;

            case 'openai':
                // OpenAI keys start with sk-
                return preg_match('/^sk-[a-zA-Z0-9_-]+$/', $key) === 1;

            case 'gemini':
                // Gemini keys are ~39 characters alphanumeric
                return preg_match('/^[a-zA-Z0-9_-]{30,50}$/', $key) === 1;

            default:
                return false;
        }
    }

    /**
     * Sanitize a string for safe output
     */
    public static function sanitize(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Validate file upload
     */
    public static function validateUpload(array $file, array $allowedTypes, int $maxSize = 10485760): array
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
}
