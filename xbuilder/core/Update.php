<?php
/**
 * XBuilder Update Manager
 *
 * Handles:
 * - Checking for updates from GitHub main branch
 * - Downloading and applying updates
 * - Backup and rollback functionality
 * - Preserving user data (site/, storage/, config)
 *
 * Update Method:
 * - Checks VERSION file from GitHub main branch
 * - Downloads main branch archive if newer version available
 * - No dependency on GitHub Releases
 *
 * Safety features:
 * - Creates backup before update
 * - Verifies downloaded files
 * - Preserves user-generated content
 * - Rollback on failure
 * - Version compatibility checking
 */

namespace XBuilder\Core;

class Update
{
    private string $rootPath;
    private string $backupPath;
    private string $githubRepo = 'Asif2BD/xBuilderCMS';
    private string $currentVersion;

    public function __construct()
    {
        $this->rootPath = dirname(__DIR__, 2);
        $this->backupPath = dirname(__DIR__) . '/storage/backups';
        $this->currentVersion = $this->getCurrentVersion();
        $this->ensureBackupDirectory();
    }

    /**
     * Ensure backup directory exists
     */
    private function ensureBackupDirectory(): void
    {
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0700, true);
        }

        // Protect with .htaccess
        $htaccessPath = $this->backupPath . '/.htaccess';
        if (!file_exists($htaccessPath)) {
            file_put_contents($htaccessPath, "Order deny,allow\nDeny from all\n");
        }
    }

    /**
     * Get current version
     */
    private function getCurrentVersion(): string
    {
        $versionFile = $this->rootPath . '/VERSION';
        if (file_exists($versionFile)) {
            return trim(file_get_contents($versionFile));
        }
        return '0.0.0';
    }

    /**
     * Check for updates from GitHub main branch
     */
    public function checkForUpdates(): array
    {
        try {
            // Check VERSION file from main branch
            $versionUrl = "https://raw.githubusercontent.com/{$this->githubRepo}/main/VERSION";

            $ch = curl_init($versionUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT => 'XBuilder-Update-Checker',
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => true
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 || !$response) {
                return [
                    'available' => false,
                    'current_version' => $this->currentVersion,
                    'error' => 'Failed to check for updates from GitHub'
                ];
            }

            $latestVersion = trim($response);

            // Validate version format (e.g., 0.7.6)
            if (!preg_match('/^\d+\.\d+\.\d+$/', $latestVersion)) {
                return [
                    'available' => false,
                    'current_version' => $this->currentVersion,
                    'error' => 'Invalid version format from GitHub'
                ];
            }

            $hasUpdate = version_compare($latestVersion, $this->currentVersion, '>');

            // Fetch CHANGELOG for release notes
            $changelogUrl = "https://raw.githubusercontent.com/{$this->githubRepo}/main/CHANGELOG.md";
            $changelog = @file_get_contents($changelogUrl);

            // Extract changelog for latest version
            $changelogNotes = '';
            if ($changelog) {
                if (preg_match("/## \[{$latestVersion}\].*?\n(.*?)(?=\n## \[|$)/s", $changelog, $matches)) {
                    $changelogNotes = trim($matches[1]);
                }
            }

            return [
                'available' => $hasUpdate,
                'current_version' => $this->currentVersion,
                'latest_version' => $latestVersion,
                'changelog' => $changelogNotes,
                'download_url' => "https://github.com/{$this->githubRepo}/archive/refs/heads/main.zip"
            ];

        } catch (\Exception $e) {
            return [
                'available' => false,
                'current_version' => $this->currentVersion,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Download update from GitHub
     */
    private function downloadUpdate(string $downloadUrl): ?string
    {
        try {
            $tempFile = sys_get_temp_dir() . '/xbuilder-update-' . time() . '.zip';

            $ch = curl_init($downloadUrl);
            $fp = fopen($tempFile, 'w+');

            curl_setopt_array($ch, [
                CURLOPT_FILE => $fp,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT => 'XBuilder-Update-Downloader',
                CURLOPT_TIMEOUT => 300, // 5 minutes
                CURLOPT_SSL_VERIFYPEER => true
            ]);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            fclose($fp);

            if (!$result || $httpCode !== 200) {
                unlink($tempFile);
                return null;
            }

            // Verify ZIP file
            $zip = new \ZipArchive();
            if ($zip->open($tempFile) !== true) {
                unlink($tempFile);
                return null;
            }
            $zip->close();

            return $tempFile;

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Create backup of current installation
     */
    private function createBackup(): ?string
    {
        try {
            $backupFile = $this->backupPath . '/backup-' . $this->currentVersion . '-' . time() . '.zip';
            $zip = new \ZipArchive();

            if ($zip->open($backupFile, \ZipArchive::CREATE) !== true) {
                return null;
            }

            // Files to backup (exclude storage and site - user data)
            $filesToBackup = [
                'index.php',
                '.htaccess',
                'VERSION',
                'README.md',
                'CHANGELOG.md',
                'LICENSE',
                'xbuilder/router.php',
                'xbuilder/core/',
                'xbuilder/api/',
                'xbuilder/views/'
            ];

            foreach ($filesToBackup as $item) {
                $path = $this->rootPath . '/' . $item;

                if (is_file($path)) {
                    $zip->addFile($path, $item);
                } elseif (is_dir($path)) {
                    $this->addDirToZip($zip, $path, $item);
                }
            }

            $zip->close();
            return $backupFile;

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Add directory to ZIP recursively
     */
    private function addDirToZip(\ZipArchive $zip, string $dir, string $zipPath): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $relativePath = $zipPath . '/' . substr($filePath, strlen($dir) + 1);

            if ($file->isDir()) {
                $zip->addEmptyDir($relativePath);
            } else {
                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    /**
     * Apply update from downloaded ZIP (main branch archive)
     */
    private function applyUpdate(string $zipFile): bool
    {
        try {
            $zip = new \ZipArchive();
            if ($zip->open($zipFile) !== true) {
                error_log("[XBuilder Update] Failed to open ZIP file");
                return false;
            }

            // Extract to temporary directory
            $tempDir = sys_get_temp_dir() . '/xbuilder-extract-' . time();
            if (!$zip->extractTo($tempDir)) {
                error_log("[XBuilder Update] Failed to extract ZIP");
                $zip->close();
                return false;
            }
            $zip->close();

            // Find the extracted directory (GitHub main branch creates "xBuilderCMS-main")
            $extractedDirs = glob($tempDir . '/*', GLOB_ONLYDIR);
            if (empty($extractedDirs)) {
                error_log("[XBuilder Update] No directories found in extracted ZIP");
                $this->deleteDir($tempDir);
                return false;
            }
            $sourceDir = $extractedDirs[0];

            error_log("[XBuilder Update] Source directory: " . $sourceDir);

            // Files/dirs to update (preserve user data: site/, storage/)
            $itemsToUpdate = [
                'index.php',
                '.htaccess',
                'VERSION',
                'README.md',
                'CHANGELOG.md',
                'LICENSE',
                'xbuilder/router.php',
                'xbuilder/core/',
                'xbuilder/api/',
                'xbuilder/views/'
            ];

            // Copy files
            foreach ($itemsToUpdate as $item) {
                $source = $sourceDir . '/' . $item;
                $dest = $this->rootPath . '/' . $item;

                if (!file_exists($source)) {
                    error_log("[XBuilder Update] Source not found: $source");
                    continue;
                }

                if (is_file($source)) {
                    if (!copy($source, $dest)) {
                        error_log("[XBuilder Update] Failed to copy file: $item");
                    } else {
                        error_log("[XBuilder Update] Copied file: $item");
                    }
                } elseif (is_dir($source)) {
                    $this->copyDir($source, $dest);
                    error_log("[XBuilder Update] Copied directory: $item");
                }
            }

            // Cleanup
            $this->deleteDir($tempDir);
            if (file_exists($zipFile)) {
                unlink($zipFile);
            }

            error_log("[XBuilder Update] Update applied successfully");
            return true;

        } catch (\Exception $e) {
            error_log("[XBuilder Update] Exception during apply: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Copy directory recursively
     */
    private function copyDir(string $source, string $dest): void
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $targetPath = $dest . '/' . substr($file->getRealPath(), strlen($source) + 1);

            if ($file->isDir()) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0755, true);
                }
            } else {
                copy($file->getRealPath(), $targetPath);
            }
        }
    }

    /**
     * Delete directory recursively
     */
    private function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir($dir);
    }

    /**
     * Rollback to backup
     */
    public function rollback(string $backupFile): bool
    {
        if (!file_exists($backupFile)) {
            return false;
        }

        try {
            $zip = new \ZipArchive();
            if ($zip->open($backupFile) !== true) {
                return false;
            }

            // Extract directly to root (overwrites current files)
            $zip->extractTo($this->rootPath);
            $zip->close();

            return true;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Perform full update process
     */
    public function performUpdate(): array
    {
        // Step 1: Check for updates
        $updateInfo = $this->checkForUpdates();
        if (!$updateInfo['available']) {
            return [
                'success' => false,
                'error' => 'No updates available'
            ];
        }

        // Step 2: Create backup
        $backupFile = $this->createBackup();
        if (!$backupFile) {
            return [
                'success' => false,
                'error' => 'Failed to create backup'
            ];
        }

        // Step 3: Download update
        $zipFile = $this->downloadUpdate($updateInfo['download_url']);
        if (!$zipFile) {
            return [
                'success' => false,
                'error' => 'Failed to download update'
            ];
        }

        // Step 4: Apply update
        $applied = $this->applyUpdate($zipFile);
        if (!$applied) {
            // Rollback on failure
            $this->rollback($backupFile);
            return [
                'success' => false,
                'error' => 'Failed to apply update (rolled back)'
            ];
        }

        // Step 5: Verify update
        $newVersion = $this->getCurrentVersion();
        if ($newVersion !== $updateInfo['latest_version']) {
            // Rollback on version mismatch
            $this->rollback($backupFile);
            return [
                'success' => false,
                'error' => 'Version mismatch after update (rolled back)'
            ];
        }

        return [
            'success' => true,
            'old_version' => $this->currentVersion,
            'new_version' => $newVersion,
            'backup_file' => basename($backupFile)
        ];
    }

    /**
     * Clean old backups (keep last 5)
     */
    public function cleanOldBackups(): void
    {
        $backups = glob($this->backupPath . '/backup-*.zip');
        if (count($backups) <= 5) {
            return;
        }

        // Sort by modification time
        usort($backups, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });

        // Delete oldest backups
        $toDelete = array_slice($backups, 0, count($backups) - 5);
        foreach ($toDelete as $file) {
            unlink($file);
        }
    }

    /**
     * Get list of backups
     */
    public function getBackups(): array
    {
        $backups = glob($this->backupPath . '/backup-*.zip');
        $result = [];

        foreach ($backups as $file) {
            $result[] = [
                'file' => basename($file),
                'size' => filesize($file),
                'created' => filemtime($file)
            ];
        }

        // Sort by creation time (newest first)
        usort($result, function($a, $b) {
            return $b['created'] - $a['created'];
        });

        return $result;
    }
}
