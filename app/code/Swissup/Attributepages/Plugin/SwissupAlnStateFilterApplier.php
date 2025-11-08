<?php

namespace Swissup\Attributepages\Plugin;

class SwissupAlnStateFilterApplier
{
    private \Swissup\Attributepages\Helper\Page\View $pageViewHelper;

    public function __construct(\Swissup\Attributepages\Helper\Page\View $pageViewHelper)
    {
        $this->pageViewHelper = $pageViewHelper;
    }

    public function afterGetFilters(
        \Swissup\Ajaxlayerednavigation\Model\Layer\Filter\Attribute\StateFilterApplier $subject,
        array $filters
    ) {
        if ($currentPage = $this->pageViewHelper->getRegistryObject('attributepages_current_page')) {
            $filters[] = [
                'id' => $currentPage->getAttribute()->getAttributeId(),
                'code' => $currentPage->getAttribute()->getAttributeCode(),
                'values' => [$currentPage->getOptionId()],
            ];
        }

        return $filters;
    }
}
