<?php

namespace Swissup\Navigationpro\Installer\Command;

use Magento\Store\Model\Store;

class Menu
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $configWriter;

    /**
     * @var \Magento\Store\Model\ResourceModel\Store\CollectionFactory
     */
    private $storeCollectionFactory;

    /**
     * @var \Swissup\Navigationpro\Model\MenuFactory
     */
    private $menuFactory;

    /**
     * @var \Swissup\Navigationpro\Model\Menu\BuilderFactory
     */
    private $menuBuilderFactory;

    /**
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory
     * @param \Swissup\Navigationpro\Model\MenuFactory $menuFactory
     * @param \Swissup\Navigationpro\Model\Menu\BuilderFactory $menuBuilderFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory,
        \Swissup\Navigationpro\Model\MenuFactory $menuFactory,
        \Swissup\Navigationpro\Model\Menu\BuilderFactory $menuBuilderFactory
    ) {
        $this->configWriter = $configWriter;
        $this->storeCollectionFactory = $storeCollectionFactory;
        $this->menuFactory = $menuFactory;
        $this->menuBuilderFactory = $menuBuilderFactory;
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * Create new menu and enable it in the config, if needed
     * If duplicate is found - do nothing.
     *
     * @param \Swissup\Marketplace\Installer\Request $request
     */
    public function execute($request)
    {
        $this->logger->info('Navigationpro: Create menu');

        $isSingleStore = count($request->getStoreIds()) === 1;
        $isAllStores = in_array(Store::DEFAULT_STORE_ID, $request->getStoreIds());

        // group stores by root_category_id for easier menu creation
        $collection = $this->storeCollectionFactory
            ->create()
            ->addRootCategoryIdAttribute()
            ->setLoadDefault(true);

        if (!$isAllStores || !$isSingleStore) {
            $collection->addFieldToFilter('store_id', ['in' => $request->getStoreIds()]);
        }

        $rootCategoryIds = [];
        foreach ($collection as $store) {
            $id = $store->getRootCategoryId();

            if (!isset($rootCategoryIds[$id])) {
                $rootCategoryIds[$id] = [];
            }

            $rootCategoryIds[$id][] = $store->getId();
        }

        foreach ($request->getParams() as $data) {
            if (!isset($data['settings']['identifier']) || !isset($data['type'])) {
                continue;
            }

            foreach ($rootCategoryIds as $categoryId => $storeIds) {
                if ($isSingleStore && $isAllStores && count($rootCategoryIds) === 1) {
                    $storeIds = [Store::DEFAULT_STORE_ID];
                }

                $builder = $this->menuBuilderFactory
                    ->create($data['type'])
                    ->setStoreIds($storeIds);

                if (isset($data['theme_id'])) {
                    $builder->setThemeId($data['theme_id']);
                }

                if (isset($data['settings'])) {
                    $builder->updateSettings($data['settings']);
                }

                if (isset($data['widget_settings'])) {
                    $builder->updateWidgetSettings($data['widget_settings']);
                }

                // use unique menu name if multiple root category ids are found
                $name = $builder->getSettings('identifier');
                if (count($rootCategoryIds) > 1) {
                    $name .= '_cat' . $categoryId;
                    $builder->updateSettings([
                        'identifier' => $name
                    ]);
                }

                $menu = $this->menuFactory->create()->load($name, 'identifier');

                if (!$menu->getId()) {
                    if (isset($data['items'])) {
                        $builder->updateItems($data['items']);
                    }

                    $builder->setRootCategoryId($categoryId);

                    try {
                        $menu = $builder->save();
                    } catch (\Exception $e) {
                        $this->logger->warning($e->getMessage());
                        continue;
                    }
                }

                if (!empty($data['activate'])) {
                    try {
                        $this->activate($menu, $storeIds);
                    } catch (\Exception $e) {
                        $this->logger->warning($e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * Activate menu per store ids
     *
     * @param \Swissup\Navigationpro\Model\Menu $menu
     * @param array $storeIds
     */
    private function activate(\Swissup\Navigationpro\Model\Menu $menu, $storeIds)
    {
        $this->saveConfig('navigationpro/top/identifier', $menu->getIdentifier(), $storeIds);
    }

    /**
     * Save single config section
     *
     * @param string $path
     * @param mixed  $value
     * @param array  $storeIds
     */
    private function saveConfig($path, $value, $storeIds = [])
    {
        foreach ($storeIds as $storeId) {
            if (!$storeId) {
                $writeScope = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
            } else {
                $writeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
            }
            $this->configWriter->save($path, $value, $writeScope, $storeId);
        }
    }
}
