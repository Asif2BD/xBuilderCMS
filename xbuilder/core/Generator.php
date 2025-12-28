<?php
/**
 * XBuilder Generator Class
 *
 * Handles writing generated HTML to the site directory
 * and managing the published website.
 *
 * Combined best practices:
 * - Instance-based for better DI/testing
 * - Backup before publish (safety)
 * - Asset directories (css, js, images)
 * - ZIP export for download
 * - HTML validation and optimization
 * - Metadata extraction
 */

namespace XBuilder\Core;

class Generator
{
    private string $siteDir;
    private string $storageDir;
    private const PREVIEW_FILE = '_preview.html';
    private const INDEX_FILE = 'index.html';
    private const BACKUP_DIR = 'backups';

    public function __construct()
    {
        $this->siteDir = dirname(__DIR__, 2) . '/site';
        $this->storageDir = dirname(__DIR__) . '/storage';
        $this->ensureDirectories();
    }

    /**
     * Ensure all required directories exist
     */
    private function ensureDirectories(): void
    {
        $dirs = [
            $this->siteDir,
            $this->siteDir . '/css',
            $this->siteDir . '/js',
            $this->siteDir . '/images',
            $this->storageDir . '/' . self::BACKUP_DIR
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        // Create .gitkeep in site directory
        $gitkeep = $this->siteDir . '/.gitkeep';
        if (!file_exists($gitkeep)) {
            file_put_contents($gitkeep, '');
        }
    }

    /**
     * Get the site directory path
     */
    public function getSiteDir(): string
    {
        return $this->siteDir;
    }

    /**
     * Get the preview file path
     */
    public function getPreviewPath(): string
    {
        return $this->siteDir . '/' . self::PREVIEW_FILE;
    }

    /**
     * Get the index file path
     */
    public function getIndexPath(): string
    {
        return $this->siteDir . '/' . self::INDEX_FILE;
    }

    /**
     * Save HTML as preview (not published yet)
     */
    public function savePreview(string $html): bool
    {
        $result = file_put_contents($this->getPreviewPath(), $html, LOCK_EX);
        return $result !== false;
    }

    /**
     * Get the current preview HTML
     */
    public function getPreview(): ?string
    {
        $path = $this->getPreviewPath();

        if (!file_exists($path)) {
            return null;
        }

        return file_get_contents($path);
    }

    /**
     * Publish the preview to the live site
     * Creates a backup of any existing site first
     */
    public function publish(): bool
    {
        $previewPath = $this->getPreviewPath();
        $indexPath = $this->getIndexPath();

        if (!file_exists($previewPath)) {
            throw new \RuntimeException('No preview to publish');
        }

        // Backup existing site if it exists
        if (file_exists($indexPath)) {
            $this->createBackup();
        }

        // Read preview content
        $html = file_get_contents($previewPath);

        if ($html === false) {
            throw new \RuntimeException('Failed to read preview');
        }

        // Write to index.html
        $result = file_put_contents($indexPath, $html, LOCK_EX);

        if ($result === false) {
            throw new \RuntimeException('Failed to publish site');
        }

        return true;
    }

    /**
     * Save HTML directly to index.html (for publish API)
     */
    public function saveHtml(string $html, string $filename = 'index.html'): array
    {
        // Validate HTML
        $errors = $this->validateHtml($html);
        if (!empty($errors)) {
            return [
                'success' => false,
                'error' => 'Invalid HTML: ' . implode(', ', $errors)
            ];
        }

        // Backup existing site if it exists
        $indexPath = $this->siteDir . '/' . $filename;
        if (file_exists($indexPath)) {
            $this->createBackup();
        }

        // Write the file
        $result = file_put_contents($indexPath, $html, LOCK_EX);

        if ($result === false) {
            return [
                'success' => false,
                'error' => 'Failed to write file'
            ];
        }

        return [
            'success' => true,
            'path' => $indexPath,
            'size' => $result,
            'url' => '/' . $filename
        ];
    }

    /**
     * Create a backup of the current published site
     */
    private function createBackup(): bool
    {
        $indexPath = $this->getIndexPath();

        if (!file_exists($indexPath)) {
            return false;
        }

        $backupDir = $this->storageDir . '/' . self::BACKUP_DIR;
        $backupFile = $backupDir . '/site_' . date('Y-m-d_His') . '.html';

        return copy($indexPath, $backupFile);
    }

    /**
     * List available backups
     */
    public function listBackups(): array
    {
        $backupDir = $this->storageDir . '/' . self::BACKUP_DIR;
        $files = glob($backupDir . '/site_*.html');

        $backups = [];
        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'path' => $file,
                'size' => filesize($file),
                'date' => filemtime($file)
            ];
        }

        // Sort by date descending
        usort($backups, fn($a, $b) => $b['date'] - $a['date']);

        return $backups;
    }

    /**
     * Restore a backup
     */
    public function restoreBackup(string $filename): bool
    {
        $backupDir = $this->storageDir . '/' . self::BACKUP_DIR;
        $backupPath = $backupDir . '/' . basename($filename);

        if (!file_exists($backupPath)) {
            throw new \RuntimeException('Backup not found');
        }

        // Backup current before restoring
        if ($this->isPublished()) {
            $this->createBackup();
        }

        $html = file_get_contents($backupPath);
        return file_put_contents($this->getIndexPath(), $html, LOCK_EX) !== false;
    }

    /**
     * Check if a site has been published
     */
    public function isPublished(): bool
    {
        return file_exists($this->getIndexPath());
    }

    /**
     * Check if a preview exists
     */
    public function hasPreview(): bool
    {
        return file_exists($this->getPreviewPath());
    }

    /**
     * Get the published site HTML
     */
    public function getPublished(): ?string
    {
        $path = $this->getIndexPath();

        if (!file_exists($path)) {
            return null;
        }

        return file_get_contents($path);
    }

    /**
     * Delete the preview
     */
    public function deletePreview(): bool
    {
        $path = $this->getPreviewPath();

        if (file_exists($path)) {
            return unlink($path);
        }

        return true;
    }

    /**
     * Delete the published site
     */
    public function deletePublished(): bool
    {
        $path = $this->getIndexPath();

        if (file_exists($path)) {
            return unlink($path);
        }

        return true;
    }

    /**
     * Validate HTML structure
     */
    public function validateHtml(string $html): array
    {
        $errors = [];

        // Check for DOCTYPE
        if (stripos($html, '<!DOCTYPE html>') === false && stripos($html, '<!doctype html>') === false) {
            $errors[] = 'Missing DOCTYPE declaration';
        }

        // Check for html tag
        if (stripos($html, '<html') === false) {
            $errors[] = 'Missing <html> tag';
        }

        // Check for head tag
        if (stripos($html, '<head>') === false && stripos($html, '<head ') === false) {
            $errors[] = 'Missing <head> tag';
        }

        // Check for body tag
        if (stripos($html, '<body>') === false && stripos($html, '<body ') === false) {
            $errors[] = 'Missing <body> tag';
        }

        // Check for closing tags
        if (stripos($html, '</html>') === false) {
            $errors[] = 'Missing closing </html> tag';
        }

        return $errors;
    }

    /**
     * Optimize HTML (minify, etc.)
     */
    public function optimizeHtml(string $html): string
    {
        // Remove HTML comments (except IE conditionals)
        $html = preg_replace('/<!--(?!\[if).*?-->/s', '', $html);

        // Remove extra whitespace between tags
        $html = preg_replace('/>\s+</', '> <', $html);

        // Collapse multiple spaces to single space
        $html = preg_replace('/\s+/', ' ', $html);

        // Restore newlines after certain tags for readability
        $html = preg_replace('/<\/(head|body|html|section|header|footer|nav|main|article|aside|div)>/i', "</\\1>\n", $html);

        return trim($html);
    }

    /**
     * Extract metadata from HTML
     */
    public function extractMetadata(string $html): array
    {
        $metadata = [
            'title' => null,
            'description' => null,
            'fonts' => [],
            'colors' => [],
        ];

        // Extract title
        if (preg_match('/<title>(.+?)<\/title>/is', $html, $matches)) {
            $metadata['title'] = trim(strip_tags($matches[1]));
        }

        // Extract meta description
        if (preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\'](.*?)["\']/i', $html, $matches)) {
            $metadata['description'] = trim($matches[1]);
        }

        // Extract Google Fonts
        if (preg_match_all('/fonts\.googleapis\.com\/css2?\?family=([^"\'&]+)/i', $html, $matches)) {
            foreach ($matches[1] as $font) {
                $fontName = urldecode(explode(':', $font)[0]);
                $fontName = str_replace('+', ' ', $fontName);
                $metadata['fonts'][] = $fontName;
            }
            $metadata['fonts'] = array_unique($metadata['fonts']);
        }

        // Extract Tailwind colors used (basic detection)
        if (preg_match_all('/(?:bg|text|border)-([a-z]+-\d{2,3})/i', $html, $matches)) {
            $metadata['colors'] = array_unique($matches[1]);
        }

        return $metadata;
    }

    /**
     * Get site statistics
     */
    public function getStats(): array
    {
        $stats = [
            'has_preview' => $this->hasPreview(),
            'is_published' => $this->isPublished(),
            'preview_size' => 0,
            'published_size' => 0,
            'backup_count' => 0,
            'last_published' => null,
        ];

        if ($stats['has_preview']) {
            $stats['preview_size'] = filesize($this->getPreviewPath());
        }

        if ($stats['is_published']) {
            $path = $this->getIndexPath();
            $stats['published_size'] = filesize($path);
            $stats['last_published'] = date('c', filemtime($path));
        }

        $stats['backup_count'] = count($this->listBackups());

        return $stats;
    }

    /**
     * Export site as ZIP
     */
    public function exportAsZip(): ?string
    {
        if (!$this->isPublished()) {
            return null;
        }

        $zipPath = $this->storageDir . '/export_' . date('Y-m-d_His') . '.zip';
        $zip = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            return null;
        }

        // Add index.html
        $zip->addFile($this->getIndexPath(), 'index.html');

        // Add any other files in site directory (css, js, images)
        $this->addDirectoryToZip($zip, $this->siteDir, '');

        $zip->close();

        return $zipPath;
    }

    /**
     * Recursively add directory to ZIP
     */
    private function addDirectoryToZip(\ZipArchive $zip, string $dir, string $basePath): void
    {
        $files = scandir($dir);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $dir . '/' . $file;
            $zipPath = $basePath ? $basePath . '/' . $file : $file;

            // Skip preview and gitkeep
            if ($file === self::PREVIEW_FILE || $file === '.gitkeep') {
                continue;
            }

            if (is_dir($filePath)) {
                $zip->addEmptyDir($zipPath);
                $this->addDirectoryToZip($zip, $filePath, $zipPath);
            } else {
                $zip->addFile($filePath, $zipPath);
            }
        }
    }

    /**
     * Clean old backups (keep last N)
     */
    public function cleanOldBackups(int $keepCount = 10): int
    {
        $backups = $this->listBackups();
        $deleted = 0;

        if (count($backups) <= $keepCount) {
            return 0;
        }

        // Delete oldest backups beyond keepCount
        $toDelete = array_slice($backups, $keepCount);

        foreach ($toDelete as $backup) {
            if (unlink($backup['path'])) {
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Check if site exists (alias for isPublished)
     */
    public function siteExists(): bool
    {
        return $this->isPublished();
    }

    /**
     * Check if preview exists (alias for hasPreview)
     */
    public function previewExists(): bool
    {
        return $this->hasPreview();
    }

    /**
     * Get current HTML (alias for getPublished)
     */
    public function getCurrentHtml(): ?string
    {
        return $this->getPublished();
    }

    /**
     * Get preview HTML (alias for getPreview)
     */
    public function getPreviewHtml(): ?string
    {
        return $this->getPreview();
    }

    /**
     * Publish preview to main site (alias)
     */
    public function publishPreview(): array
    {
        try {
            $this->publish();
            return [
                'success' => true,
                'url' => '/'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
