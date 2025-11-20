<?php
namespace Emu24\CreditLimit\Model;

use Emu24\CreditLimit\Model\ResourceModel\CreditReport as CreditReportResource;
use Emu24\CreditLimit\Model\ResourceModel\CreditReport\CollectionFactory;

class CreditReportRepository
{
    private $creditReportFactory;
    private $resource;
    private $collectionFactory;

    public function __construct(
        CreditReportFactory $creditReportFactory,
        CreditReportResource $resource,
        CollectionFactory $collectionFactory
    ) {
        $this->creditReportFactory = $creditReportFactory;
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
    }

    public function save(CreditReport $report): void
    {
        $this->resource->save($report);
    }

    public function getLatestByCustomerId(int $customerId): ?CreditReport
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('customer_id', $customerId);
        $collection->setOrder('created_at', 'DESC');
        $collection->setPageSize(1);

        return $collection->getFirstItem()->getId() ? $collection->getFirstItem() : null;
    }
}
