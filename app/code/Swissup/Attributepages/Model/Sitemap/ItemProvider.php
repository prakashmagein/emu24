<?php

namespace Swissup\Attributepages\Model\Sitemap;

use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use Magento\Sitemap\Helper\Data as Helper;
use Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory;

class ItemProvider implements ItemProviderInterface
{
    /**
     * @var SitemapItemInterfaceFactory
     */
    private $itemFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @param SitemapItemInterfaceFactory $itemFactory
     * @param CollectionFactory           $collectionFactory
     * @param Helper                      $helper
     */
    public function __construct(
        SitemapItemInterfaceFactory $itemFactory,
        CollectionFactory $collectionFactory,
        Helper $helper
    ) {
        $this->itemFactory = $itemFactory;
        $this->collectionFactory = $collectionFactory;
        $this->helper = $helper;
    }

    /**
     * @param  int        $storeId
     * @return Collection
     */
    public function getCollection($storeId) {
        $attributes = $this->getAllowedAttributes($storeId);

        $collection = $this->collectionFactory->create()
            ->addUseForAttributePageFilter()
            ->addStoreFilter($storeId)
            ->addFilter('attribute_id', ['in' => $attributes], 'public')
            ->setOrder('attribute_id', 'ASC')
            ->setOrder('main_table.title', 'ASC');

        return $collection;
    }

    /**
     * @return array
     */
    private function getAllowedAttributes($storeId)
    {
        $collection = $this->collectionFactory->create()
            ->addAttributeOnlyFilter()
            ->addStoreFilter($storeId)
            ->addUseForAttributePageFilter();

        return $collection->getColumnValues('attribute_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getItems($storeId)
    {
        $collection = $this->getCollection($storeId);
        $items = array_map(function ($page) use ($storeId) {
            if (!$page->getParentPage($storeId) && $page->isOptionBasedPage()) {
                return null;
            }

            return $this->itemFactory->create([
                'url' => $page->getRelativeUrl(),
                'updatedAt' => $page->getUpdateTime(),
                'images' => $page->getImages(),
                'priority' => $this->helper->getPagePriority($storeId),
                'changeFrequency' => $this->helper->getPageChangefreq($storeId),
            ]);
        }, $collection->getItems());

        return array_filter($items);
    }
}
