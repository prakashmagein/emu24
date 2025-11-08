<?php

namespace Swissup\Attributepages\Plugin;

class SwissupAjaxlayerednavigationFilterAttribute
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
     * @param \Swissup\Ajaxlayerednavigation\Model\ResourceModel\Layer\Filter\Attribute $subject
     * @param array $stateFilters
     * @return array|null
     */
    public function beforeSetStateFilters(
        \Swissup\Ajaxlayerednavigation\Model\ResourceModel\Layer\Filter\Attribute $subject,
        $stateFilters
    ) {
        $currentPage = $this->pageViewHelper->getRegistryObject('attributepages_current_page');

        if (!$currentPage) {
            return null;
        }

        $stateFilters[] = [
            'id' => $currentPage->getAttribute()->getAttributeId(),
            'code' => $currentPage->getAttribute()->getAttributeCode(),
            'values' => [$currentPage->getOptionId()],
        ];

        return [$stateFilters];
    }
}
