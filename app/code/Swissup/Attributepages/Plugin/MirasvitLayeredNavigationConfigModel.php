<?php

namespace Swissup\Attributepages\Plugin;

class MirasvitLayeredNavigationConfigModel
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
     * @param \Mirasvit\LayeredNavigation\Model\Config
     * @param boolean $result
     * @return boolean
     */
    public function afterIsAjaxEnabled(
        \Mirasvit\LayeredNavigation\Model\Config $subject,
        $result
    ) {
        $currentPage = $this->pageViewHelper->getRegistryObject('attributepages_current_page');

        if (!$currentPage) {
            return $result;
        }

        return false;
    }
}
