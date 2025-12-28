<?php
/**
 * XBuilder Config Class
 * 
 * Handles application configuration and state
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
     * Load configuration
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
     * Save configuration
     */
    private function save(): bool
    {
        $dir = dirname($this->configPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }
        
        $result = file_put_contents(
            $this->configPath,
            json_encode($this->config, JSON_PRETTY_PRINT)
        );
        
        if ($result !== false) {
            chmod($this->configPath, 0600);
            return true;
        }
        
        return false;
    }
    
    /**
     * Get a config value
     */
    public function get(string $key, $default = null)
    {
        $config = $this->load();
        return $config[$key] ?? $default;
    }
    
    /**
     * Set a config value
     */
    public function set(string $key, $value): bool
    {
        $this->load();
        $this->config[$key] = $value;
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
     * Mark setup as complete
     */
    public function completeSetup(string $passwordHash, string $aiProvider): bool
    {
        $this->load();
        $this->config['setup_complete'] = true;
        $this->config['password_hash'] = $passwordHash;
        $this->config['ai_provider'] = $aiProvider;
        $this->config['created_at'] = date('c');
        $this->config['site_generated'] = false;
        
        return $this->save();
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
     * Get all config (for debugging, excludes sensitive data)
     */
    public function getPublicConfig(): array
    {
        $config = $this->load();
        
        // Remove sensitive data
        unset($config['password_hash']);
        
        return $config;
    }
}
