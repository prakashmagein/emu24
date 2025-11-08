<?php

namespace Swissup\SoldTogether\Model;

class CustomerIndexer extends AbstractIndexer
{
    /**
     * @var string
     */
    protected $linkType = 'customer';

    /**
     * {@inheritdoc}
     */
    protected function collectLinks($order, array &$links)
    {
        $this->cronProcessor->collectCustomerLinks($order, $links);
    }
}
