<?php

namespace Swissup\SoldTogether\Console\Command;

class OrderReindexCommand extends AbstractReindexCommand
{
    /**
     * @var string
     */
    protected $relationName = 'Frequently Bought Together';

    /**
     * @var string
     */
    protected $objectName = 'order';

    /**
     * @param \Swissup\SoldTogether\Model\OrderIndexer $orderIndexer
     */
    public function __construct(
        \Swissup\SoldTogether\Model\OrderIndexer $orderIndexer,
        $pageSize = 10
    ) {
        $this->indexer = $orderIndexer;
        $this->pageSize = $pageSize;
        parent::__construct();
    }
}
