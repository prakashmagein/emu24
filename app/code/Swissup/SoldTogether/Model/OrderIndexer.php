<?php

namespace Swissup\SoldTogether\Model;

class OrderIndexer extends AbstractIndexer
{
    /**
     * @var string
     */
    protected $linkType = 'order';

    /**
     * {@inheritdoc}
     */
    protected function collectLinks($order, array &$links)
    {
        $this->cronProcessor->collectOrderLinks($order, $links);
    }
}
