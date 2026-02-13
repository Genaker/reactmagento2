<?php

namespace React\React\Plugin;

use Magento\Deploy\Service\DeployStaticContent;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Psr\Log\LoggerInterface;

/**
 * Plugin to copy custom static files after deployment
 */
class PostDeployCopy
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var WriteInterface
     */
    private $staticDirectory;
    
    /**
     * @param Filesystem $filesystem
     * @param LoggerInterface $logger
     */
    public function __construct(
        Filesystem $filesystem,
        LoggerInterface $logger
    ) {
        $this->filesystem = $filesystem;
        $this->logger = $logger;
        $this->staticDirectory = $filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
    }

    /**
     * After plugin for deploy method
     *
     * @param DeployStaticContent $subject
     * @param void $result
     * @param array $options
     * @return void
     */
    public function afterDeploy(DeployStaticContent $subject, $result, array $options)
    {
        try {
            $this->copyCustomStaticFiles();
            $this->logger->info('Custom static files copied successfully after deployment');
        } catch (\Exception $e) {
            $this->logger->error('Failed to copy custom static files: ' . $e->getMessage());
        }
    }

    /**
     * Copy custom static files from module to main static directory
     * 
     * @return void
     */
    private function copyCustomStaticFiles()
    {
        $sourcePath = dirname(__DIR__) . '/pub/static';
        $targetPath = BP . '/pub/static';
        
        if (!is_dir($sourcePath)) {
            $this->logger->warning('Source directory does not exist: ' . $sourcePath);
            return;
        }

        $this->copyDirectory($sourcePath, $targetPath);
    }

    /**
     * Recursively copy directory contents with security checks
     *
     * @param string $source
     * @param string $destination
     * @return void
     */
    private function copyDirectory($source, $destination)
    {
        // Use RecursiveDirectoryIterator instead of opendir/readdir for better security
        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $item) {
                $sourcePath = $item->getPathname();
                $relativePath = substr($sourcePath, strlen($source) + 1);
                $destPath = $destination . '/' . $relativePath;
                
                // Security: Prevent symlink traversal attacks
                if (is_link($sourcePath)) {
                    $this->logger->warning('Skipping symlink: ' . $sourcePath);
                    continue;
                }
                
                if ($item->isDir()) {
                    if (!is_dir($destPath)) {
                        mkdir($destPath, 0755, true);
                    }
                } else {
                    $this->copyFile($sourcePath, $destPath);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Error copying directory: ' . $e->getMessage());
        }
    }

    /**
     * Copy a single file
     *
     * @param string $source
     * @param string $destination
     */
    private function copyFile($source, $destination)
    {
        $destinationDir = dirname($destination);
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        if (copy($source, $destination)) {
            $this->logger->debug('Copied file: ' . $source . ' -> ' . $destination);
        } else {
            $this->logger->error('Failed to copy file: ' . $source . ' -> ' . $destination);
        }
    }
}