<?php

namespace Swissup\Gdpr\Model\ResourceModel;

class ClientConsent extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_gdpr_client_consent', 'entity_id');
    }
}
