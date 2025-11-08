<?php

namespace Swissup\Askit\Block\Question\Listing;

use Swissup\Askit\Block\Question\Listing;

class Customer extends Listing
{
    /**
     *
     * @return \Swissup\Askit\Model\ResourceModel\Question\Collection
     */
    public function getCollection()
    {
        if (empty($this->collection)) {
            $collection = parent::getCollection();

            $customerId = (int) $this->getCustomerSession()->getId();
            $collection->addCustomerFilter($customerId);

            $this->collection = $collection;
        }

        return $this->collection;
    }
}
