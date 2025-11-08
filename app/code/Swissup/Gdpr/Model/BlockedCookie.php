<?php

namespace Swissup\Gdpr\Model;

class BlockedCookie extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Swissup\Gdpr\Model\ResourceModel\BlockedCookie::class);
    }
}
