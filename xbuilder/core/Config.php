<?php
/**
 * XBuilder Configuration Class
 *
 * Handles application configuration and state.
 * Configuration is stored as JSON in the storage directory.
 *
 * Combined best practices:
 * - Instance-based for better DI/testing
 * - Dot notation for nested values
 * - Site generation tracking
 * - Site metadata storage
 */

namespace XBuilder\Core;

class Config
{
    private string $configPath;
    private ?array $config = null;

    public function __construct()
    {
        $this->configPath = dirname(__DIR__) . '/storage/config.json';
    }

    /**
     * Load configuration from file
     */
    private function load(): array
    {
        if ($this->config !== null) {
            return $this->config;
        }

        if (!file_exists($this->configPath)) {
            $this->config = [];
            return $this->config;
        }

        $content = file_get_contents($this->configPath);
        $this->config = json_decode($content, true) ?? [];

        return $this->config;
    }

    /**
     * Save configuration to file
     */
    private function save(): bool
    {
        $dir = dirname($this->configPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        $result = file_put_contents(
            $this->configPath,
            json_encode($this->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            LOCK_EX
        );

        if ($result !== false) {
            chmod($this->configPath, 0600);
            return true;
        }

        return false;
    }

    /**
     * Get a config value (supports dot notation)
     */
    public function get(string $key, $default = null)
    {
        $config = $this->load();

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
     * Set a config value (supports dot notation)
     */
    public function set(string $key, $value): bool
    {
        $this->load();

        // Support dot notation for nested values
        $keys = explode('.', $key);
        $ref = &$this->config;

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

        return $this->save();
    }

    /**
     * Check if setup is complete
     */
    public function isSetupComplete(): bool
    {
        return $this->get('setup_complete', false) === true;
    }

    /**
     * Complete setup with password and AI provider
     */
    public function completeSetup(string $passwordHash, string $aiProvider): bool
    {
        $this->load();
        $this->config['setup_complete'] = true;
        $this->config['password_hash'] = $passwordHash;
        $this->config['ai_provider'] = $aiProvider;
        $this->config['created_at'] = date('c');
        $this->config['site_generated'] = false;
        $this->config['version'] = $this->getAppVersion();

        return $this->save();
    }

    /**
     * Get XBuilder application version
     */
    public function getAppVersion(): string
    {
        $versionFile = dirname(__DIR__, 2) . '/VERSION';

        if (file_exists($versionFile)) {
            $version = trim(file_get_contents($versionFile));
            if (!empty($version)) {
                return $version;
            }
        }

        return '0.6.0'; // Fallback version
    }

    /**
     * Get the installed version from config
     */
    public function getInstalledVersion(): string
    {
        return $this->get('version', '0.0.0');
    }

    /**
     * Get the configured AI provider
     */
    public function getAiProvider(): ?string
    {
        return $this->get('ai_provider');
    }

    /**
     * Set the AI provider
     */
    public function setAiProvider(string $provider): bool
    {
        return $this->set('ai_provider', $provider);
    }

    /**
     * Get password hash
     */
    public function getPasswordHash(): ?string
    {
        return $this->get('password_hash');
    }

    /**
     * Check if site has been generated
     */
    public function isSiteGenerated(): bool
    {
        return $this->get('site_generated', false) === true;
    }

    /**
     * Mark site as generated
     */
    public function markSiteGenerated(): bool
    {
        return $this->set('site_generated', true);
    }

    /**
     * Store site metadata
     */
    public function setSiteMetadata(array $metadata): bool
    {
        return $this->set('site_metadata', $metadata);
    }

    /**
     * Get site metadata
     */
    public function getSiteMetadata(): array
    {
        return $this->get('site_metadata', []);
    }

    /**
     * Get all config (excludes sensitive data)
     */
    public function getPublicConfig(): array
    {
        $config = $this->load();

        // Remove sensitive data
        unset($config['password_hash']);

        return $config;
    }

    /**
     * Reset configuration (for testing)
     */
    public function reset(): void
    {
        $this->config = null;

        if (file_exists($this->configPath)) {
            unlink($this->configPath);
        }
    }

    /**
     * Get raw config array
     */
    public function toArray(): array
    {
        return $this->load();
    }
}
