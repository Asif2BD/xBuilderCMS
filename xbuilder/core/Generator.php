<?php
/**
 * XBuilder Generator Class
 *
 * Handles writing generated HTML to the site directory
 * and managing the published website.
 */

namespace XBuilder\Core;

class Generator
{
    private const SITE_DIR = 'site';
    private const PREVIEW_FILE = 'preview.html';
    private const INDEX_FILE = 'index.html';

    /**
     * Get the site directory path
     */
    public static function getSiteDir(): string
    {
        return XBUILDER_ROOT . '/' . self::SITE_DIR;
    }

    /**
     * Get the preview file path
     */
    public static function getPreviewPath(): string
    {
        return self::getSiteDir() . '/' . self::PREVIEW_FILE;
    }

    /**
     * Get the index file path
     */
    public static function getIndexPath(): string
    {
        return self::getSiteDir() . '/' . self::INDEX_FILE;
    }

    /**
     * Save HTML as preview (not published yet)
     */
    public static function savePreview(string $html): bool
    {
        $dir = self::getSiteDir();

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $result = file_put_contents(self::getPreviewPath(), $html, LOCK_EX);
        return $result !== false;
    }

    /**
     * Get the current preview HTML
     */
    public static function getPreview(): ?string
    {
        $path = self::getPreviewPath();

        if (!file_exists($path)) {
            return null;
        }

        return file_get_contents($path);
    }

    /**
     * Publish the preview to the live site
     */
    public static function publish(): bool
    {
        $previewPath = self::getPreviewPath();
        $indexPath = self::getIndexPath();

        if (!file_exists($previewPath)) {
            throw new \RuntimeException('No preview to publish');
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
     * Check if a site has been published
     */
    public static function isPublished(): bool
    {
        return file_exists(self::getIndexPath());
    }

    /**
     * Check if a preview exists
     */
    public static function hasPreview(): bool
    {
        return file_exists(self::getPreviewPath());
    }

    /**
     * Get the published site HTML
     */
    public static function getPublished(): ?string
    {
        $path = self::getIndexPath();

        if (!file_exists($path)) {
            return null;
        }

        return file_get_contents($path);
    }

    /**
     * Delete the preview
     */
    public static function deletePreview(): bool
    {
        $path = self::getPreviewPath();

        if (file_exists($path)) {
            return unlink($path);
        }

        return true;
    }

    /**
     * Delete the published site
     */
    public static function deletePublished(): bool
    {
        $path = self::getIndexPath();

        if (file_exists($path)) {
            return unlink($path);
        }

        return true;
    }

    /**
     * Validate HTML structure
     */
    public static function validateHtml(string $html): array
    {
        $errors = [];

        // Check for DOCTYPE
        if (stripos($html, '<!DOCTYPE html>') === false) {
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

        // Check for viewport meta tag
        if (stripos($html, 'viewport') === false) {
            $errors[] = 'Missing viewport meta tag (not mobile-friendly)';
        }

        // Check for title
        if (!preg_match('/<title>.+<\/title>/i', $html)) {
            $errors[] = 'Missing or empty title tag';
        }

        return $errors;
    }

    /**
     * Optimize HTML (minify, etc.)
     */
    public static function optimizeHtml(string $html): string
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
    public static function extractMetadata(string $html): array
    {
        $metadata = [
            'title' => null,
            'description' => null,
            'fonts' => [],
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
        }

        return $metadata;
    }

    /**
     * Export site as ZIP
     */
    public static function exportAsZip(): ?string
    {
        if (!self::isPublished()) {
            return null;
        }

        $zipPath = XBUILDER_STORAGE . '/export_' . date('Y-m-d_His') . '.zip';
        $zip = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            return null;
        }

        // Add index.html
        $zip->addFile(self::getIndexPath(), 'index.html');

        // Add any other files in site directory
        $dir = self::getSiteDir();
        $files = glob($dir . '/*');

        foreach ($files as $file) {
            $filename = basename($file);
            if ($filename !== self::PREVIEW_FILE && $filename !== '.gitkeep') {
                $zip->addFile($file, $filename);
            }
        }

        $zip->close();

        return $zipPath;
    }
}
