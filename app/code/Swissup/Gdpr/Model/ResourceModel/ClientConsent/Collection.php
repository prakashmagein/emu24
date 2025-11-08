<?php

namespace Swissup\Gdpr\Model\ResourceModel\ClientConsent;

class Collection extends \Swissup\Gdpr\Model\ResourceModel\Client\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Swissup\Gdpr\Model\ClientConsent::class,
            \Swissup\Gdpr\Model\ResourceModel\ClientConsent::class
        );
    }
}
