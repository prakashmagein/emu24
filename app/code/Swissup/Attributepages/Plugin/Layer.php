<?php

namespace Swissup\Attributepages\Plugin;

class Layer
{
    /**
     * @var \Swissup\Attributepages\Helper\Page\View $pageViewHelper
     */
    private $pageViewHelper;

    /**
     * @param \Swissup\Attributepages\Helper\Page\View $pageViewHelper
     */
    public function __construct(
        \Swissup\Attributepages\Helper\Page\View $pageViewHelper
    ) {
        $this->pageViewHelper = $pageViewHelper;
    }

    /**
     * @param \Magento\Catalog\Model\Layer $subject
     * @param mixed $collection
     * @return mixed
     */
    public function beforePrepareProductCollection(
        \Magento\Catalog\Model\Layer $subject,
        $collection
    ) {
        $currentPage = $this->getCurrentPage();
        if (!$currentPage || !$currentPage->getOptionId()) {
            return;
        }

        $this->applyCurrentPageFilter($collection);
        $this->applyAdditionalFilters($collection);
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return void
     */
    private function applyCurrentPageFilter($collection)
    {
        $currentPage = $this->getCurrentPage();
        $collection->addFieldToFilter(
            $currentPage->getAttribute()->getAttributeCode(),
            $currentPage->getOptionId()
        );
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return void
     */
    private function applyAdditionalFilters($collection)
    {
        foreach ($this->getCurrentPage()->getAttributeFilters() as $code => $value) {
            $collection->addFieldToFilter($code, $value);
        }
    }

    /**
     * @return \Swissup\Attributepages\Model\Entity|null
     */
    private function getCurrentPage()
    {
        return $this->pageViewHelper->getRegistryObject('attributepages_current_page');
    }
}
