<?php
/**
 * XBuilder Configuration Class
 *
 * Handles application configuration storage and retrieval.
 * Configuration is stored as JSON in the storage directory.
 */

namespace XBuilder\Core;

class Config
{
    private const CONFIG_FILE = 'config.json';
    private static ?array $config = null;

    /**
     * Get the path to the config file
     */
    private static function getConfigPath(): string
    {
        return XBUILDER_STORAGE . '/' . self::CONFIG_FILE;
    }

    /**
     * Load configuration from file
     */
    public static function load(): array
    {
        if (self::$config !== null) {
            return self::$config;
        }

        $path = self::getConfigPath();

        if (!file_exists($path)) {
            self::$config = [];
            return self::$config;
        }

        $content = file_get_contents($path);
        $config = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid configuration file');
        }

        self::$config = $config;
        return self::$config;
    }

    /**
     * Save configuration to file
     */
    public static function save(array $config): bool
    {
        $path = self::getConfigPath();
        $dir = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        $json = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            throw new \RuntimeException('Failed to encode configuration');
        }

        $result = file_put_contents($path, $json, LOCK_EX);

        if ($result === false) {
            throw new \RuntimeException('Failed to save configuration');
        }

        chmod($path, 0600);
        self::$config = $config;

        return true;
    }

    /**
     * Get a configuration value
     */
    public static function get(string $key, $default = null)
    {
        $config = self::load();

        // Support dot notation for nested values
        $keys = explode('.', $key);
        $value = $config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Set a configuration value
     */
    public static function set(string $key, $value): bool
    {
        $config = self::load();

        // Support dot notation for nested values
        $keys = explode('.', $key);
        $ref = &$config;

        foreach ($keys as $i => $k) {
            if ($i === count($keys) - 1) {
                $ref[$k] = $value;
            } else {
                if (!isset($ref[$k]) || !is_array($ref[$k])) {
                    $ref[$k] = [];
                }
                $ref = &$ref[$k];
            }
        }

        return self::save($config);
    }

    /**
     * Check if setup is complete
     */
    public static function isSetupComplete(): bool
    {
        $config = self::load();
        return !empty($config['password_hash']) && !empty($config['ai_provider']);
    }

    /**
     * Get the configured AI provider
     */
    public static function getAiProvider(): ?string
    {
        return self::get('ai_provider');
    }

    /**
     * Get the encrypted API key for a provider
     */
    public static function getApiKey(string $provider): ?string
    {
        $keyPath = XBUILDER_STORAGE . '/keys/' . $provider . '.key';

        if (!file_exists($keyPath)) {
            return null;
        }

        $encrypted = file_get_contents($keyPath);
        return Security::decrypt($encrypted);
    }

    /**
     * Save an API key (encrypted)
     */
    public static function saveApiKey(string $provider, string $key): bool
    {
        $keyPath = XBUILDER_STORAGE . '/keys/' . $provider . '.key';
        $dir = dirname($keyPath);

        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        $encrypted = Security::encrypt($key);
        $result = file_put_contents($keyPath, $encrypted, LOCK_EX);

        if ($result === false) {
            return false;
        }

        chmod($keyPath, 0600);
        return true;
    }

    /**
     * Delete an API key
     */
    public static function deleteApiKey(string $provider): bool
    {
        $keyPath = XBUILDER_STORAGE . '/keys/' . $provider . '.key';

        if (file_exists($keyPath)) {
            return unlink($keyPath);
        }

        return true;
    }

    /**
     * Initial setup configuration
     */
    public static function setup(string $provider, string $apiKey, string $password): bool
    {
        // Validate provider
        $validProviders = ['claude', 'openai', 'gemini'];
        if (!in_array($provider, $validProviders)) {
            throw new \InvalidArgumentException('Invalid AI provider');
        }

        // Validate API key format
        if (!Security::validateApiKeyFormat($provider, $apiKey)) {
            throw new \InvalidArgumentException('Invalid API key format for ' . $provider);
        }

        // Validate password strength
        if (strlen($password) < 8) {
            throw new \InvalidArgumentException('Password must be at least 8 characters');
        }

        // Save API key (encrypted)
        if (!self::saveApiKey($provider, $apiKey)) {
            throw new \RuntimeException('Failed to save API key');
        }

        // Save configuration
        $config = [
            'ai_provider' => $provider,
            'password_hash' => Security::hashPassword($password),
            'setup_time' => date('c'),
            'version' => '1.0.0',
        ];

        return self::save($config);
    }

    /**
     * Reset the configuration (for testing)
     */
    public static function reset(): void
    {
        self::$config = null;
        $path = self::getConfigPath();

        if (file_exists($path)) {
            unlink($path);
        }

        // Delete all API keys
        $keysDir = XBUILDER_STORAGE . '/keys/';
        if (is_dir($keysDir)) {
            $files = glob($keysDir . '*.key');
            foreach ($files as $file) {
                if (basename($file) !== 'encryption.key') {
                    unlink($file);
                }
            }
        }
    }
}
