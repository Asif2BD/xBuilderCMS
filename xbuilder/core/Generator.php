<?php
/**
 * XBuilder Generator Class
 * 
 * Handles writing generated HTML to the site folder
 * and managing site assets
 */

namespace XBuilder\Core;

class Generator
{
    private string $sitePath;
    
    public function __construct()
    {
        $this->sitePath = dirname(dirname(__DIR__)) . '/site';
    }
    
    /**
     * Ensure site directory exists
     */
    private function ensureDirectory(): void
    {
        if (!is_dir($this->sitePath)) {
            mkdir($this->sitePath, 0755, true);
        }
        
        // Create subdirectories
        $dirs = ['assets', 'assets/css', 'assets/js', 'assets/images'];
        foreach ($dirs as $dir) {
            $path = $this->sitePath . '/' . $dir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }
    
    /**
     * Save HTML to site
     */
    public function saveHtml(string $html, string $filename = 'index.html'): array
    {
        $this->ensureDirectory();
        
        // Validate HTML
        if (!$this->validateHtml($html)) {
            return [
                'success' => false,
                'error' => 'Invalid HTML structure'
            ];
        }
        
        // Clean up HTML
        $html = $this->cleanHtml($html);
        
        // Save the file
        $filePath = $this->sitePath . '/' . $filename;
        $result = file_put_contents($filePath, $html);
        
        if ($result === false) {
            return [
                'success' => false,
                'error' => 'Failed to write file'
            ];
        }
        
        return [
            'success' => true,
            'path' => $filePath,
            'size' => $result,
            'url' => '/' . $filename
        ];
    }
    
    /**
     * Validate basic HTML structure
     */
    private function validateHtml(string $html): bool
    {
        // Check for basic HTML structure
        $hasDoctype = stripos($html, '<!DOCTYPE html>') !== false || stripos($html, '<!doctype html>') !== false;
        $hasHtmlTag = stripos($html, '<html') !== false && stripos($html, '</html>') !== false;
        $hasHead = stripos($html, '<head') !== false && stripos($html, '</head>') !== false;
        $hasBody = stripos($html, '<body') !== false && stripos($html, '</body>') !== false;
        
        return $hasDoctype && $hasHtmlTag && $hasHead && $hasBody;
    }
    
    /**
     * Clean up HTML
     */
    private function cleanHtml(string $html): string
    {
        // Remove any markdown code block markers that might have slipped through
        $html = preg_replace('/^```[\w-]*\s*/m', '', $html);
        $html = preg_replace('/```\s*$/m', '', $html);
        
        // Ensure proper DOCTYPE
        if (stripos($html, '<!DOCTYPE html>') === false && stripos($html, '<!doctype html>') === false) {
            $html = "<!DOCTYPE html>\n" . $html;
        }
        
        return trim($html);
    }
    
    /**
     * Create a preview version (same as main but can be used for preview iframe)
     */
    public function savePreview(string $html): array
    {
        $this->ensureDirectory();
        
        // Validate HTML
        if (!$this->validateHtml($html)) {
            return [
                'success' => false,
                'error' => 'Invalid HTML structure'
            ];
        }
        
        // Save to preview file
        $html = $this->cleanHtml($html);
        $previewPath = $this->sitePath . '/_preview.html';
        $result = file_put_contents($previewPath, $html);
        
        if ($result === false) {
            return [
                'success' => false,
                'error' => 'Failed to write preview file'
            ];
        }
        
        return [
            'success' => true,
            'path' => $previewPath,
            'url' => '/site/_preview.html'
        ];
    }
    
    /**
     * Publish preview to main site
     */
    public function publishPreview(): array
    {
        $previewPath = $this->sitePath . '/_preview.html';
        $mainPath = $this->sitePath . '/index.html';
        
        if (!file_exists($previewPath)) {
            return [
                'success' => false,
                'error' => 'No preview to publish'
            ];
        }
        
        // Backup existing if any
        if (file_exists($mainPath)) {
            $backupPath = $this->sitePath . '/_backup_' . date('Y-m-d_H-i-s') . '.html';
            copy($mainPath, $backupPath);
        }
        
        // Copy preview to main
        $result = copy($previewPath, $mainPath);
        
        if (!$result) {
            return [
                'success' => false,
                'error' => 'Failed to publish'
            ];
        }
        
        return [
            'success' => true,
            'url' => '/'
        ];
    }
    
    /**
     * Check if site exists
     */
    public function siteExists(): bool
    {
        return file_exists($this->sitePath . '/index.html');
    }
    
    /**
     * Check if preview exists
     */
    public function previewExists(): bool
    {
        return file_exists($this->sitePath . '/_preview.html');
    }
    
    /**
     * Get current site HTML
     */
    public function getCurrentHtml(): ?string
    {
        $mainPath = $this->sitePath . '/index.html';
        
        if (file_exists($mainPath)) {
            return file_get_contents($mainPath);
        }
        
        return null;
    }
    
    /**
     * Get preview HTML
     */
    public function getPreviewHtml(): ?string
    {
        $previewPath = $this->sitePath . '/_preview.html';
        
        if (file_exists($previewPath)) {
            return file_get_contents($previewPath);
        }
        
        return null;
    }
    
    /**
     * Delete site
     */
    public function deleteSite(): bool
    {
        if (!is_dir($this->sitePath)) {
            return true;
        }
        
        // Remove all files in site directory
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->sitePath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        
        return rmdir($this->sitePath);
    }
    
    /**
     * Get site statistics
     */
    public function getStats(): array
    {
        $stats = [
            'exists' => $this->siteExists(),
            'preview_exists' => $this->previewExists(),
            'files' => [],
            'total_size' => 0
        ];
        
        if (is_dir($this->sitePath)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->sitePath, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($files as $file) {
                if ($file->isFile()) {
                    $relativePath = str_replace($this->sitePath . '/', '', $file->getRealPath());
                    $stats['files'][] = [
                        'name' => $relativePath,
                        'size' => $file->getSize()
                    ];
                    $stats['total_size'] += $file->getSize();
                }
            }
        }
        
        return $stats;
    }
}
