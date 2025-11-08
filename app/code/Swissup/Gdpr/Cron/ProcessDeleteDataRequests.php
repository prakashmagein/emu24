<?php

namespace Swissup\Gdpr\Cron;

use Swissup\Gdpr\Model\ClientRequest;

class ProcessDeleteDataRequests
{
    /**
     * @var \Swissup\Gdpr\Helper\Data
     */
    private $helper;

    /**
     * @var \Swissup\Gdpr\Model\ResourceModel\ClientRequest\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Swissup\Gdpr\Model\ClientRequest\Processor
     */
    private $processor;

    /**
     * @param \Swissup\Gdpr\Helper\Data $helper
     * @param \Swissup\Gdpr\Model\ResourceModel\ClientRequest\CollectionFactory $collectionFactory
     * @param \Swissup\Gdpr\Model\ClientRequest\Processor $processor
     */
    public function __construct(
        \Swissup\Gdpr\Helper\Data $helper,
        \Swissup\Gdpr\Model\ResourceModel\ClientRequest\CollectionFactory $collectionFactory,
        \Swissup\Gdpr\Model\ClientRequest\Processor $processor
    ) {
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
        $this->processor = $processor;
    }

    /**
     * @return void
     */
    public function execute()
    {
        if (!$this->canExecute()) {
            return;
        }

        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('status', ClientRequest::STATUS_CONFIRMED)
            ->addFieldToFilter('type', ClientRequest::TYPE_DATA_DELETE)
            ->setOrder('created_at', 'ASC');

        if ($days = $this->getDaysToWait()) {
            $date = new \DateTime();
            $date->sub(new \DateInterval('P' . $days . 'D'));
            $collection->addFieldToFilter('created_at', [
                'lt' => $date
            ]);
        }

        foreach ($collection->getItems() as $item) {
            if (!$this->processor->canProcess($item)) {
                continue;
            }
            $this->processor->process($item);
        }
    }

    /**
     * Check if automation is enabled
     *
     * @return boolean
     */
    private function canExecute()
    {
        return (bool) $this->helper->getConfigValue(
            'swissup_gdpr/request/delete_data/automate'
        );
    }

    /**
     * Get days to wait before request processing
     *
     * @return integer
     */
    private function getDaysToWait()
    {
        return (int) $this->helper->getConfigValue(
            'swissup_gdpr/request/delete_data/days_to_wait'
        );
    }
}
