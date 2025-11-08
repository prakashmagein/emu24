<?php

namespace Swissup\SoldTogether\Console\Command;

class CustomerReindexCommand extends AbstractReindexCommand
{
    /**
     * @var string
     */
    protected $relationName = 'Customers Also Bought';

    /**
     * @var string
     */
    protected $objectName = 'customer';

    /**
     * @param \Swissup\SoldTogether\Model\CustomerIndexer $customerIndexer
     */
    public function __construct(
        \Swissup\SoldTogether\Model\CustomerIndexer $customerIndexer,
        $pageSize = 5
    ) {
        $this->indexer = $customerIndexer;
        $this->pageSize = $pageSize;
        parent::__construct();
    }
}
