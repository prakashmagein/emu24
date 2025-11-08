<?php
declare(strict_types=1);

namespace Swissup\Pagespeed\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Asset\File\FallbackContext as FileFallbackContext;
use Magento\Framework\Locale\ResolverInterfaceFactory;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\DesignInterfaceFactory;
use Magento\Framework\View\Design\Theme\ListInterface;
use Magento\Framework\View\Asset\RepositoryFactory;
use Magento\Framework\RequireJs\ConfigFactory;
use Swissup\Pagespeed\Helper\Config as ConfigHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Bundle
{
    /**
     * Matched file extension name for JavaScript files
     */
    const ASSET_TYPE_JS = 'js';
    /**
     * Matched file extension name for template files
     */
    const ASSET_TYPE_HTML = 'html';

    /**
     * List of supported types of static files
     *
     * @var array
     * */
    public static $availableTypes = [
        self::ASSET_TYPE_JS,
        self::ASSET_TYPE_HTML
    ];

    /**
     * @var FileFallbackContext
     */
    private $staticContext;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * Factory for Bundle object
     *
     * @see BundleInterface
     * @var \Swissup\Pagespeed\Model\Bundle\Manager\RequireJsFactory
     */
    private $bundleManagerFactory;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $ioFile;

    /**
     * @var \Swissup\Pagespeed\Model\Bundle\Manager\RequireJs
     */
    private $bundleManager;

    /**
     * Bundle constructor
     *
     * @param FileFallbackContext $staticContext
     * @param \Magento\Framework\Filesystem $appFilesystem
     * @param ConfigHelper $configHelper
     * @param \Swissup\Pagespeed\Model\Bundle\Manager\RequireJsFactory $bundleManagerFactory
     * @param \Magento\Framework\Filesystem\Io\File $ioFile
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        FileFallbackContext $staticContext,
        \Magento\Framework\Filesystem $appFilesystem,
        ConfigHelper $configHelper,
        \Swissup\Pagespeed\Model\Bundle\Manager\RequireJsFactory $bundleManagerFactory,
        \Magento\Framework\Filesystem\Io\File $ioFile
    ) {
        $this->staticContext = $staticContext;
        $this->filesystem = $appFilesystem;
        $this->configHelper = $configHelper;
        $this->bundleManagerFactory = $bundleManagerFactory;
        $this->ioFile = $ioFile;
    }

    /**
     * @return \Swissup\Pagespeed\Model\Bundle\Manager\RequireJs
     */
    private function getBundleManager()
    {
        if ($this->bundleManager === null) {

            $context = $this->staticContext;
            $areaCode = $context->getAreaCode();
            $themePath = $context->getThemePath();
            $localeCode = $context->getLocale();

            $this->bundleManager = $this->bundleManagerFactory->create([
                'area' => $areaCode,
                'theme' => $themePath,
                'locale' => $localeCode
            ]);
        }

        return $this->bundleManager;
    }

    /**
     * Deploy bundles
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deploy()
    {
        $bundleManager = $this->getBundleManager();
        $staticContentPath = $this->staticContext->getPath();
        // $bundleManager->clear();

        $rjsConfig = $this->configHelper->getRjsJsonConfig();

        $map = $rjsConfig['map']['*'] ?? [];
        $paths = $rjsConfig['paths'] ?? [];
        $paths = array_merge($map, $paths);

        $handles = isset($rjsConfig['modules']) ? $rjsConfig['modules'] : [];

        $dir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        foreach ($handles as $handle) {
            if (!isset($handle['name'])) {
                continue;
            }
            $bundleManager->setHandle($handle['name']);

            if ($bundleManager->isHandleAlreadyExist()) {
                continue;
            }

            $includeFiles = isset($handle['include']) ? $handle['include'] : [];
            if (empty($includeFiles)) {
                continue;
            }

            foreach ($includeFiles as $includeFile) {
                $includeFile = isset($paths[$includeFile]) ? $paths[$includeFile] : $includeFile;

                if (!is_string($includeFile) || substr($includeFile, -1) === '!') {
                    continue;
                }
                if (strpos($includeFile, 'text!') === 0) {
                    $includeFile = substr($includeFile, 5);
                } else {
                    $includeFile = $includeFile . '.js';
                }

                $pathInfo = $this->ioFile->getPathInfo($includeFile);
//                $contentType = pathinfo($includeFile, PATHINFO_EXTENSION);
                $contentType = isset($pathInfo['extension']) ? $pathInfo['extension'] : false;
                if (!in_array($contentType, self::$availableTypes)) {
                    continue;
                }

                $includeFilePath = $staticContentPath . '/' . $includeFile;
                if (!$dir->isExist($includeFilePath)) {
                    $includeFilePath = preg_replace('/\.js$/', '.min.js', $includeFilePath);
                    if (!$dir->isExist($includeFilePath)) {
                        foreach ($paths as $short => $path) {
                            if (is_string($short) && strpos($includeFile, $short) === 0) {
                                $includeFile = $path . substr($includeFile, strlen($short));
                            }
                        }
                    }
                }

                $includeFilePath = $staticContentPath. '/' . $includeFile;
                if (!$dir->isExist($includeFilePath)) {
                    $includeMinFile = preg_replace('/\.js$/', '.min.js', $includeFile);
                    $includeFilePath = $staticContentPath . '/' . $includeMinFile;
                    if (!$dir->isExist($includeFilePath)) {
                        continue;
                    }
                }

                $bundleManager->addFile($includeFile, $includeFilePath, $contentType);
            }
            $bundleManager->flush();
        }
    }

    /**
     * Clear bundles
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function clear()
    {
        $bundleManager = $this->getBundleManager();
        $bundleManager->clear();
    }
}
