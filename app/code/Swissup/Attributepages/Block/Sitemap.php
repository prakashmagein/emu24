<?php

namespace Swissup\Attributepages\Block;

use Magento\Framework\View\Element\Template;
use Swissup\Attributepages\Model\Entity;
use Swissup\Attributepages\Model\Sitemap\ItemProvider;

class Sitemap extends Template
{
    /**
     * @var ItemProvider
     */
    private $itemProvider;

    /**
     * @param ItemProvider      $itemProvider
     * @param Template\Context  $context
     * @param array             $data
     */
    public function __construct(
        ItemProvider $itemProvider,
        Template\Context $context,
        array $data = []
    ) {
        $this->itemProvider = $itemProvider;
        parent::__construct($context, $data);
    }

    /**
     * @return Collection
     */
    public function getCollection()
    {
        $storeId = $this->_storeManager->getStore()->getId();

        return $this->itemProvider->getCollection($storeId);
    }

    /**
     * @param  Entity $page
     * @return string
     */
    public function getItemUrl(Entity $page)
    {
        return $page->getUrl();
    }

    /**
     * @param  Entity $page
     * @return string
     */
    public function getItemName(Entity $page)
    {
        return $page->getTitle();
    }
}
