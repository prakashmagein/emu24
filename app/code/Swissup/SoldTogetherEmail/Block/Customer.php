<?php

namespace Swissup\SoldTogetherEmail\Block;

class Customer extends Order
{
    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->getData('title') ?:
            __('Customers Who Bought This Item(s) Also Bought');
    }
}
