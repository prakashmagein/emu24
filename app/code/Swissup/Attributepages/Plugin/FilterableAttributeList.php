<?php

namespace Swissup\Attributepages\Plugin;

class FilterableAttributeList
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
     * @param \Magento\Catalog\Model\Layer\Category\FilterableAttributeList $subject
     * @param mixed $collection
     * @return mixed
     */
    public function afterGetList(
        \Magento\Catalog\Model\Layer\Category\FilterableAttributeList $subject,
        $collection
    ) {
        $currentPage = $this->pageViewHelper->getRegistryObject('attributepages_current_page');
        if (!$currentPage || is_array($collection)) {
            return $collection;
        }

        $filtersToRemove = [
            $currentPage->getAttribute()->getAttributeCode(),
        ];

        foreach ($currentPage->getAttributeFilters() as $code => $value) {
            $filtersToRemove[] = $code;
        }

        foreach ($filtersToRemove as $code) {
            $item = $collection->getItemByColumnValue('attribute_code', $code);
            if ($item) {
                $collection->removeItemByKey($item->getId());
            }
        }

        return $collection;
    }
}
