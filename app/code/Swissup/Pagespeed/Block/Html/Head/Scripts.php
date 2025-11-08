<?php

namespace Swissup\Pagespeed\Block\Html\Head;

class Scripts extends \Magento\Framework\View\Element\AbstractBlock
{
    const REQUIREJS_OVERRIDE_PATH = 'Swissup_Pagespeed::js/lib/requirejs/override.js';
    const UNPACK_PATH = 'Swissup_Pagespeed::js/unpack-defer.js';
    const REQUIREJS_PRELOAD_ALL_SCRIPTS_PATH = 'Swissup_Pagespeed::js/lib/requirejs/preload-all-scripts.js';
    const INIT_COMPONENTS_IN_VIEWPORT_PATH = 'Swissup_Pagespeed::js/init-components-in-viewport.js';

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    private $pageConfig;

    /**
     *
     * @var \Swissup\Pagespeed\Helper\Config
     */
    private $configHelper;

    /**
     * @var \Swissup\Pagespeed\Model\Bundle\ManagerFactory
     */
    private $bundleManagerFactory;

    /**
     * @var \Swissup\Pagespeed\Model\Bundle\Manager
     */
    private $bundleManager;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $ioFile;

    /**
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Framework\View\Page\Config $pageConfig
     * @param \Swissup\Pagespeed\Helper\Config $configHelper
     * @param \Swissup\Pagespeed\Model\Bundle\Manager $bundleManagerFactory
     * @param \Magento\Framework\Filesystem\Io\File $ioFile
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\View\Page\Config $pageConfig,
        \Swissup\Pagespeed\Helper\Config $configHelper,
        \Swissup\Pagespeed\Model\Bundle\ManagerFactory $bundleManagerFactory,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->pageConfig = $pageConfig;
        $this->configHelper = $configHelper;
        $this->bundleManagerFactory = $bundleManagerFactory;
        $this->ioFile = $ioFile;
    }

    /**
     * @return \Magento\Framework\View\Asset\GroupedCollection
     */
    private function getAssetCollection()
    {
        return $this->pageConfig->getAssetCollection();
    }

    /**
     * @return \Magento\Framework\View\Asset\Repository
     */
    private function getAssetRepository()
    {
        return $this->_assetRepo;
    }

    /**
     * @return \Swissup\Pagespeed\Model\Bundle\Manager
     */
    private function getBundleManager()
    {
        if ($this->bundleManager === null) {
            $this->bundleManager = $this->bundleManagerFactory->create();
        }

        return $this->bundleManager;
    }

    /**
     * Include RequireJs configuration as an asset on the page
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
//        \Magento\RequireJs\Block\Html\Head::_prepareLayout
        if ($this->configHelper->isAdvancedJsBundling()) {
            $this->addAdvancedBunlingAssets();
        }

        $isInteractiveDeferEnable = $this->configHelper->isInteractiveDeferEnable();
        if ($isInteractiveDeferEnable) {
            $this->addOverrideForInteractiveLoad();// lib/requirejs/override.js
        }

        $isDeferJsUnpackEnable = $this->configHelper->isDeferJsUnpackEnable();
        if ($isDeferJsUnpackEnable) {
            $this->addDeferUnpacking();//unpack-defer.js
        }

        if ($this->configHelper->isForceRequireJsLoadingEnabled()) {
            $this->addPreloadAllScripts();// lib/requirejs/preload-all-scripts.js
        }

        if ($isInteractiveDeferEnable) {
            $this->addInitComponentsInViewport();//init-components-in-viewport.js
        }

        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    private function getAfter()
    {
        $after = \Magento\Framework\RequireJs\Config::REQUIRE_JS_FILE_NAME;
        $assetCollection = $this->getAssetCollection();
        $all = $assetCollection->getAll();

        $assetFilenames = [
            \Magento\Framework\RequireJs\Config::MIN_RESOLVER_FILENAME,
            \Magento\Framework\RequireJs\Config::MAP_FILE_NAME
        ];

        foreach ($assetFilenames as $assetFilename) {
            $pathInfo = $this->ioFile->getPathInfo($assetFilename);
            $filename = $pathInfo['filename'];
            foreach ($all as $asset) {
                if ($asset instanceof \Magento\Framework\View\Asset\File) {
                    $filePath = $asset->getFilePath();
                    if (strpos($filePath, $filename) !== false) {
                        $after = $filePath;
                    }
                }
            }
        }

        return $after;
    }

    private function addOverrideForInteractiveLoad()
    {
        $after = $this->getAfter();
        $assetCollection = $this->getAssetCollection();

        $asset = $this->getAssetRepository()->createAsset(self::REQUIREJS_OVERRIDE_PATH);
        $assetCollection->insert($asset->getFilePath(), $asset, $after);
    }

    private function addAdvancedBunlingAssets()
    {
        $after = $after = $this->getAfter();
        $assetCollection = $this->getAssetCollection();
        $bundleManager = $this->getBundleManager();

        $fullActionName = $this->getRequest()->getFullActionName();
        $fullActionName = str_replace("_", "-", $fullActionName);
        $staticAsset = false;
        $handles = ['default', $fullActionName];

        foreach ($handles as $handle) {
            $bundleAssets = $bundleManager->createBundleJsPool($handle);
            /** @var \Magento\Framework\View\Asset\File $bundleAsset */
            if (!empty($bundleAssets)) {
                foreach ($bundleAssets as $bundleAsset) {
                    $assetCollection->insert(
                        $bundleAsset->getFilePath(),
                        $bundleAsset,
                        $after
                    );
                    $after = $bundleAsset->getFilePath();
                }
            }
        }

        $staticAsset = $bundleManager->createStaticJsAsset();
        if ($staticAsset !== false) {
            $assetCollection->insert(
                $staticAsset->getFilePath(),
                $staticAsset,
                $after
            );
        }
    }

    private function addDeferUnpacking()
    {
        $assetCollection = $this->getAssetCollection();

        $asset = $this->getAssetRepository()->createAsset(self::UNPACK_PATH);
        $assetCollection->add($asset->getFilePath(), $asset);
    }

    private function addPreloadAllScripts()
    {
        $assetCollection = $this->getAssetCollection();

        $asset = $this->getAssetRepository()->createAsset(self::REQUIREJS_PRELOAD_ALL_SCRIPTS_PATH);
        $assetCollection->add($asset->getFilePath(), $asset);
    }

    private function addInitComponentsInViewport()
    {
        $assetCollection = $this->getAssetCollection();

        $asset = $this->getAssetRepository()->createAsset(self::INIT_COMPONENTS_IN_VIEWPORT_PATH);
        $assetCollection->add($asset->getFilePath(), $asset);
    }
}
