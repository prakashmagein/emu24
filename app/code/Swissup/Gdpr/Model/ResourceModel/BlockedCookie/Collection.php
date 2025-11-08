<?php

namespace Swissup\Gdpr\Model\ResourceModel\BlockedCookie;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'cookie_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Swissup\Gdpr\Model\BlockedCookie::class,
            \Swissup\Gdpr\Model\ResourceModel\BlockedCookie::class
        );
    }
}
