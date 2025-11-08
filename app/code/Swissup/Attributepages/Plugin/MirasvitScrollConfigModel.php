<?php

namespace Swissup\Attributepages\Plugin;

class MirasvitScrollConfigModel
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
     * @param \Mirasvit\Scroll\Model\ConfigProvider
     * @param boolean $result
     * @return boolean
     */
    public function afterIsEnabled(
        \Mirasvit\Scroll\Model\ConfigProvider $subject,
        $result
    ) {
        $currentPage = $this->pageViewHelper->getRegistryObject('attributepages_current_page');

        if (!$currentPage) {
            return $result;
        }

        return false;
    }
}
