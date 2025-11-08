<?php

namespace Swissup\Gdpr\Ui\DataProvider;

use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;

class AddCustomerNameToCollection implements AddFieldToCollectionInterface
{
    public function addField(Collection $collection, $field, $alias = null)
    {
        /** @var \Swissup\Gdpr\Model\ResourceModel\ClientConsent\Collection $collection */
        $collection->addCustomerNameToSelect();
    }
}
