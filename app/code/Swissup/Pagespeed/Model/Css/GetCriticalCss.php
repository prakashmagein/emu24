<?php
declare(strict_types=1);

namespace Swissup\Pagespeed\Model\Css;

use Magento\Framework\UrlInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GetCriticalCss
{
    const API_URL = 'http://pagespeed.swissuplabs.com/critical-css/generate';

    /**
     * pub/media/critical-css
     *
     * @var string
     */
    protected $storageDirName = 'critical-css';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var \Magento\Store\Api\Data\WebsiteInterface|null
     */
    private $website;

    /**
     * @var \Magento\Store\Api\Data\GroupInterface|null
     */
    private $group;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface|null
     */
    private $store;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var \Magento\Framework\HTTP\Client\CurlFactory
     */
    private $curlFactory;

    /**
     * @var string|null
     */
    private $criticalCss = '';

    /**
     * Backend Config Model Factory
     *
     * @var \Magento\Config\Model\Config\Factory
     */
    private $configFactory;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepository;

    /**
     * Static content storage directory writable interface
     *
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    private $storageDir;

    /**
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\HTTP\Client\CurlFactory $curlFactory
     * @param \Magento\Config\Model\Config\Factory $configFactory
     * @param \Magento\Framework\View\Asset\Repository $assetRepository
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\HTTP\Client\CurlFactory $curlFactory,
        \Magento\Config\Model\Config\Factory $configFactory,
        \Magento\Framework\View\Asset\Repository $assetRepository,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->storeManager = $storeManager;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->curlFactory = $curlFactory;
        $this->configFactory = $configFactory;
        $this->assetRepository = $assetRepository;
//        $directoryWrite = $filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        $directoryWrite = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->storageDir = $directoryWrite;
    }

    /**
     *
     * @param $store
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getTargetUrlsForDefaultCritical($store)
    {
        $targetUrls = [];
        $targetUrls[] =  $store->getBaseUrl() . 'noroute';
        return $targetUrls;
    }

    private function getCategoryId($store)
    {
        $collection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('entity_id') // Select only the category ID
            ->addIsActiveFilter() // Ensure the category is active
            ->setStoreId($store->getId()) // Apply store filter
        ;
        // Convert collection to an array of category IDs
        $categoryIds = $collection->getAllIds();

        // Check if any categories exist
        if (!empty($categoryIds)) {
            // Return a random category ID
            return $categoryIds[array_rand($categoryIds)];
        }

        return $store->getRootCategoryId();
    }

    /**
     *
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getTargetUrls($store)
    {
        $targetUrls = [];
//        $pathPrefix = 'Swissup_Pagespeed::css/critical/';
        $pathPrefix = 'css/critical/';
        $noRouteUrl = $store->getBaseUrl() . 'noroute';
        $targetUrls[$pathPrefix . 'default.css'] =  [$noRouteUrl];
        $targetUrls[$pathPrefix . 'cms_index_index.css'] = [
            $store->getBaseUrl(UrlInterface::URL_TYPE_WEB, true),
            '-' . $noRouteUrl
        ];
        $this->storeManager->setCurrentStore($store->getId());
        $categoryId = $this->getCategoryId($store);
        if ($categoryId) {
            $category = $this->categoryRepository->get($categoryId, $store->getId());
            $targetUrls[$pathPrefix . 'catalog_category_view.css'] = [
                $category->getUrl(),
                '-' . $noRouteUrl
            ];

            /** @var \Magento\Catalog\Model\Product $product */
            $product = $category->getProductCollection()->getLastItem();
            if ($product->getId()) {
                $targetUrls[$pathPrefix . 'catalog_product_view.css'] = [
                    $product->getProductUrl(),
                    '-' . $noRouteUrl
                ];
            }
        }
//        foreach ($targetUrls as &$targetUrl) {
//            $targetUrl = str_replace( ['magento247.local', 'localhost'], 'm2pagespeed.swissupdemo.com', $targetUrl);
////            $targetUrl = urlencode($targetUrl);
//        }

        return $targetUrls;
    }

    /**
     *
     * @param $targetUrls
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCriticalCssByUrls($targetUrls)
    {
        $criticalCss = '';
        $websiteParam  = implode(',', $targetUrls);

        /** @var \Magento\Framework\HTTP\Client\Curl $client */
        $client = $this->curlFactory->create();
        try {
            $apiUrl = self::API_URL . '?' . http_build_query([
                    'website' => $websiteParam,
                    'ignore-urls' => '1'
                ]);
            $client->get($apiUrl);
            $status = $client->getStatus();
            if ($status === 200) {
                $criticalCss = $client->getBody();
            } else {
                throw new \Magento\Framework\Exception\RemoteServiceUnavailableException(
                    __('Service Api Error %1', $status)
                );
            }
        } catch (\Laminas\Http\Exception\RuntimeException $e) {
            $criticalCss = '';
            throw new \Magento\Framework\Exception\RemoteServiceUnavailableException(
                __('Service Api Error %1', $e->getMessage())
            );
        }
        $this->criticalCss = $criticalCss;

        return $criticalCss;
    }

    public function setWebsite($website)
    {
        if (is_int($website) || is_string($website)) {
            $website = $this->storeManager->getWebsite($website);
        }
        if (!$website instanceof \Magento\Store\Api\Data\WebsiteInterface) {
            throw new \Magento\Framework\Exception\InvalidArgumentException(
                __('website argument should be instanceof WebsiteInterface')
            );
        }

        $this->website = $website;
        $this->criticalCss = '';

        return $this;
    }

    public function setGroup($group)
    {
        if (is_int($group) || is_string($group)) {
            $group = $this->storeManager->getGroup($group);
        }
        if (!$group instanceof \Magento\Store\Api\Data\GroupInterface ) {
            throw new \Magento\Framework\Exception\InvalidArgumentException(
                __('group argument should be instanceof GroupInterface')
            );
        }

        $this->group = $group;
        $this->criticalCss = '';

        return $this;
    }

    /**
     * @param $store
     * @return $this
     */
    public function setStore($store)
    {
        if (is_int($store) || is_string($store)) {
            $store = $this->storeManager->getStore($store);
        }
        if (!$store instanceof \Magento\Store\Api\Data\StoreInterface) {
            throw new \Magento\Framework\Exception\InvalidArgumentException(
                __('store argument should be instanceof StoreInterface')
            );
        }

        $this->store = $store;
        $this->criticalCss = '';

        return $this;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function generateDefault()
    {
        if (empty($this->store)) {
            throw new \Magento\Framework\Exception\InvalidArgumentException(
                __('Set store before')
            );
        }
        $urls = $this->getTargetUrlsForDefaultCritical($this->store);
        $this->getCriticalCssByUrls($urls);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCriticalCss()
    {
        return $this->criticalCss;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function saveConfig($criticalCss = '')
    {
        if (empty($criticalCss)) {
            $criticalCss = (string) $this->criticalCss;
        }
        if (empty($criticalCss)) {
            throw new \Magento\Framework\Exception\InvalidArgumentException(
                __('Critical Css is empty')
            );
        }

        $store = $this->store;
        $storeId = $this->store ? $store->getId() : null;

        $configData = [
            'section' => 'pagespeed',
            'website' => $storeId ? $store->getWebsiteId() : null,
            'store' => $storeId ? $store->getId() : null,
            'groups' => [
                'css' => [
                    'groups' => [
                        'critical' => [
                            'fields' => [
                                'enable' => [
//                                        \Swissup\Pagespeed\Helper\Config::CONFIG_XML_PATH_CSS_CRITICAL_ENABLE
                                    'value' => true
                                ],
                                'default' => [
//                                        \Swissup\Pagespeed\Helper\Config::CONFIG_XML_PATH_CSS_CRITICAL_DEFAULT
                                    'value' => $criticalCss
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];

        /** @var \Magento\Config\Model\Config $configModel */
        $configModel = $this->configFactory->create(['data' => $configData]);
        $configModel->save();

        return $this;
    }

    private function getAllStoreIds()
    {
        $storeIds = [];
        try {
            $storeId = $this->store->getId();
            if ($storeId != "0") {
                $storeIds[] = $storeId;
            } elseif ($this->group->getId() != "0") {
                $storeIds = $this->group->getStoreIds();
            } elseif ($this->website->getId() != "0") {
                $storeIds = $this->website->getStoreIds();
            } else {
                // $storeIds[] = $this->store->getId();//0
                $websites = $this->storeManager->getWebsites();
                foreach ($websites as $website) {
                    foreach ($website->getGroups() as $group) {
                        foreach ($group->getStores() as $store) {
                            $storeIds[] = $store->getId();
                        }
                    }
                }
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
        }

        return array_unique($storeIds);
    }

    public function generateForThemes()
    {
        $storeIds  = $this->getAllStoreIds();
        foreach ($storeIds as $storeId) {
            $store = $this->storeManager->getStore($storeId);
            $themeId = (string) $store->getConfig(
                \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID
            );
            $locale = (string) $store->getConfig(
                \Magento\Directory\Helper\Data::XML_PATH_DEFAULT_LOCALE
            );
            $targetUrls = $this->getTargetUrls($store);
            foreach ($targetUrls as $path => $urls) {
                $asset = $this->assetRepository->createAsset($path, [
                    'area' => 'frontend',
                    'locale' => $locale,
                    'themeId' => $themeId,
                    '_secure' => 'false'
                ]);
                $relativePath = '/' . $this->storageDirName . '/' . $asset->getPath();
                $storageFile = $this->storageDir->openFile($relativePath);
                $criticalCss = $this->getCriticalCssByUrls($urls);
                $storageFile->write($criticalCss);
            }
        }

        return $this;
    }
}
