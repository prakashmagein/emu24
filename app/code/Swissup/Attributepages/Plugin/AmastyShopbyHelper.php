<?php

namespace Swissup\Attributepages\Plugin;

class AmastyShopbyHelper
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
     * @param \Amasty\Shopby\Helper\Data $subject
     * @param boolean $result
     * @return boolean
     */
    public function afterIsAjaxEnabled(
        \Amasty\Shopby\Helper\Data $subject,
        $result
    ) {
        $currentPage = $this->pageViewHelper->getRegistryObject('attributepages_current_page');

        if (!$currentPage) {
            return $result;
        }

        return false;
    }
}
