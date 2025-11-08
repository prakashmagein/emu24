<?php

namespace Swissup\Gdpr\Ui\DataProvider\Cookie;

use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;

class AddDescriptionToCollection implements AddFieldToCollectionInterface
{
    public function addField(Collection $collection, $field, $alias = null)
    {
        /** @var \Swissup\Gdpr\Model\ResourceModel\Cookie\CustomCollection $collection */
        $collection->addDescriptionToSelect();
    }
}
