<?php

namespace Swissup\Pagespeed\Plugin\Block\Html\Header;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Asset\File\NotFoundException;

class CriticalCssPlugin
{
    /**
     * @var \Swissup\Pagespeed\Helper\Config
     */
    private $config;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Magento\Framework\View\Layout
     */
    private $layout;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    private $pageConfig;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepo;

    /**
     * @var \Swissup\Pagespeed\Model\View\Asset\PlaceholderReplacer
     */
    private $placeholderReplacer;

    /**
     * Static content storage directory writable interface
     *
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    private $storageDir;

    /**
     * @param \Swissup\Pagespeed\Helper\Config $config
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\View\Layout $layout
     * @param \Magento\Framework\View\Page\Config $pageConfig
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Swissup\Pagespeed\Model\View\Asset\PlaceholderReplacer $placeholderReplacer
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Swissup\Pagespeed\Helper\Config $config,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\View\Layout $layout,
        \Magento\Framework\View\Page\Config $pageConfig,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Swissup\Pagespeed\Model\View\Asset\PlaceholderReplacer $placeholderReplacer,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->config = $config;
        /** @var \Magento\Framework\App\Request\Http $request */
        $this->request = $request;
        $this->layout = $layout;
        $this->pageConfig = $pageConfig;
        $this->assetRepo = $assetRepo;
        $this->placeholderReplacer = $placeholderReplacer;
        $directoryWrite = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $this->storageDir = $directoryWrite;
    }

    /**
     * Will return the currect page layout.
     *
     * @return string The current page layout.
     */
    private function getCurrentPageLayout()
    {
        /** @var string|null $currentPageLayout */
        $currentPageLayout = $this->pageConfig->getPageLayout();

        if ($currentPageLayout === null) {
            /** @var \Magento\Framework\View\Model\Layout\Merge $update */
            $update = $this->layout->getUpdate();
            return $update->getPageLayout();
        }

        return $currentPageLayout;
    }

    /**
     *
     * @return string
     */
    private function getFullActionName()
    {
        return $this->request->getFullActionName();
    }

    /**
     *
     * @param  \Magento\Theme\Block\Html\Header\CriticalCss $subject
     * @param  string $result
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCriticalCssData(
        \Magento\Theme\Block\Html\Header\CriticalCss $subject,
        $result
    ) {
        if ($this->config->isCriticalCssThemeHanleMergeEnable()) {
            $paths = [
//                'Swissup_Pagespeed::css/critical/',
                'css/critical/'
            ];
            foreach ($this->getAssetGroups() as $group) {
                foreach ($group as $filename) {
                    foreach ($paths as $path) {
                        try {
                            $relativePathInStaticDir = $path . $filename;
                            $asset = $this->assetRepo->createAsset(
                                $relativePathInStaticDir,
                                ['_secure' => 'false']
                            );
                            $relativePathInMediaDir = '/critical-css/' . $asset->getPath();
                            // try to get content from storage in media/critical-css folder
                            // else get content from asset pub/static folder
                            if ($this->storageDir->isFile($relativePathInMediaDir)) {
                                $result .= $this->storageDir->readFile($relativePathInMediaDir);
                            } else {
                                $result .= $asset->getContent();
                            }

                            break 2;
                        } catch (LocalizedException|NotFoundException $e) {
                            //
                        }
                    }
                }
            }

            $result = $this->processPlaceholders($result);
        }

        if ($this->config->isCriticalCssEnable()
            && $this->config->isUseCssCriticalPathEnable()
        ) {
            $configContent = $this->config->getDefaultCriticalCss();
            $result .= $configContent;
        }

        return $result;
    }

    /**
     * @param string $styles
     * @return string
     */
    private function processPlaceholders($styles)
    {
        return $this->placeholderReplacer->process($styles);
    }

    /**
     * @return array
     */
    private function getAssetGroups()
    {
        return [
            [
                'default.css',
            ],
            [
                // first match will be used only
                $this->getFullActionName(). '-' . $this->getCurrentPageLayout() . '.css',
                $this->getFullActionName() . '.css',
            ],
        ];
    }
}
