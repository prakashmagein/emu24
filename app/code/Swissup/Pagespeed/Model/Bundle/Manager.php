<?php

namespace Swissup\Pagespeed\Model\Bundle;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Asset\File\FallbackContext as FileFallbackContext;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\File\ReadFactory;
use Swissup\Pagespeed\Helper\Config as ConfigHelper;
use Magento\Deploy\Package\BundleInterface;
use Swissup\Pagespeed\Model\Bundle\Manager\RequireJs;
use Magento\Framework\View\Asset\Minification;

class Manager
{
    /**
     * @var AssetRepository
     */
    private $assetRepo;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Swissup\Pagespeed\Model\BundleFactory
     */
    private $bundleServiceFactory;

    /**
     * Helper class for static files minification related processes
     *
     * @var Minification
     */
    private $minification;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $ioFile;

    /**
     * @param AssetRepository $assetRepo
     * @param \Magento\Framework\Filesystem $appFilesystem
     * @param \Swissup\Pagespeed\Model\BundleFactory $bundleServiceFactory
     * @param Minification $minification
     * @param \Magento\Framework\Filesystem\Io\File $ioFile
     */
    public function __construct(
        AssetRepository $assetRepo,
        \Magento\Framework\Filesystem $appFilesystem,
        \Swissup\Pagespeed\Model\BundleFactory $bundleServiceFactory,
        Minification $minification,
        \Magento\Framework\Filesystem\Io\File $ioFile
    ) {
        $this->assetRepo = $assetRepo;
        $this->filesystem = $appFilesystem;
        $this->bundleServiceFactory = $bundleServiceFactory;
        $this->minification = $minification;
        $this->ioFile = $ioFile;

        $this->ensureSourceFiles();
    }

    /**
     *
     * @return string
     */
    private function getSubDir()
    {
        return 'Swissup_Pagespeed/js/bundle';
    }

    /**
     * Create a view assets representing the bundle js functionality
     *
     * @param  string $handle
     * @return \Magento\Framework\View\Asset\File[]
     */
    public function createBundleJsPool($handle)
    {
        $bundles = [];
        $libDir = $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW);
        /** @var \Magento\Framework\View\Asset\File\FallbackContext $context */
        $context = $this->assetRepo->getStaticViewFileContext();

        $bundleDir = $context->getPath() . '/' . $this->getSubDir();

        if (!$libDir->isExist($bundleDir)) {
            return [];
        }

        $isMinifiedEnabled = $this->minification->isEnabled('js');
        foreach ($libDir->read($bundleDir) as $bundleFile) {
            if ($this->getFileExtension($bundleFile) !== 'js'
                || strpos($bundleFile, $bundleDir . '/' . $handle . '-bundle') !== 0
            ) {
                continue;
            }

            $isMinifiedFilename = $this->minification->isMinifiedFilename($bundleFile);
            if (($isMinifiedEnabled && !$isMinifiedFilename)
                || (!$isMinifiedEnabled && $isMinifiedFilename)
            ) {
                continue;
            }
            $relPath = $libDir->getRelativePath($bundleFile);
            $bundles[] = $this->assetRepo->createArbitrary($relPath, '');
        }
        return $bundles;
    }

    /**
     * instead pathinfo($path, PATHINFO_EXTENSION)
     * @param string $path
     * @return string
     */
    private function getFileExtension($path)
    {
        $pathInfo = $this->ioFile->getPathInfo($path);
        return isset($pathInfo['extension']) ? $pathInfo['extension'] : false;
    }

    /**
     * Create a view asset representing the static js functionality
     *
     * @return \Magento\Framework\View\Asset\File|false
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createStaticJsAsset()
    {
        return $this->assetRepo->createAsset(
            \Magento\Framework\RequireJs\Config::STATIC_FILE_NAME
        );
    }

    /**
     *
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function ensureSourceFiles()
    {
        $bundleService = $this->bundleServiceFactory->create([
            'staticContext' => $this->assetRepo->getStaticViewFileContext()
        ]);
        $bundleService->deploy();
    }
}
