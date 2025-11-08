<?php
namespace Swissup\Easybanner\Model\ResourceModel;

/**
 * BannerStore mysql resource
 */
class BannerStore extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_easybanner_banner_store', 'store_id');
    }
}
