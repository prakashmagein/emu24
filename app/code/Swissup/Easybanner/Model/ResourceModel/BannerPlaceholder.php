<?php
namespace Swissup\Easybanner\Model\ResourceModel;

/**
 * Banner mysql resource
 */
class BannerPlaceholder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_easybanner_banner_placeholder', 'placeholder_id');
    }
}
