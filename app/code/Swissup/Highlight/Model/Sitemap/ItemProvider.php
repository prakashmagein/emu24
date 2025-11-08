<?php

namespace Swissup\Highlight\Model\Sitemap;

use Swissup\Highlight\Model\Page\Collection;
use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Sitemap\Helper\Data as Helper;

class ItemProvider implements ItemProviderInterface
{
    /**
     * @var SitemapItemInterfaceFactory
     */
    protected $itemFactory;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param SitemapItemInterfaceFactory $itemFactory
     * @param Helper                      $helper
     */
    public function __construct(
        SitemapItemInterfaceFactory $itemFactory,
        Helper $helper
    ) {
        $this->itemFactory = $itemFactory;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems($storeId)
    {
        $collection = ObjectManager::getInstance()->create(Collection::class);
        $items = array_map(function ($page) use ($storeId) {
            return $this->itemFactory->create([
                'url' => $page->getUrl(),
                'updatedAt' => $page->getUpdateTime(),
                'images' => $page->getImages(),
                'priority' => $this->helper->getPagePriority($storeId),
                'changeFrequency' => $this->helper->getPageChangefreq($storeId),
            ]);
        }, $collection->getItems());

        return $items;
    }
}
