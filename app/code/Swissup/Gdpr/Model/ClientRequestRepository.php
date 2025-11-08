<?php

namespace Swissup\Gdpr\Model;

use Swissup\Gdpr\Model\ClientRequest;

class ClientRequestRepository
{
    /**
     * @var \Swissup\Gdpr\Model\ClientRequestFactory
     */
    private $factory;

    /**
     * @var \Swissup\Gdpr\Model\ResourceModel\ClientRequest\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param \Swissup\Gdpr\Model\ClientRequestFactory $factory
     * @param \Swissup\Gdpr\Model\ResourceModel\ClientRequest\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Swissup\Gdpr\Model\ClientRequestFactory $factory,
        \Swissup\Gdpr\Model\ResourceModel\ClientRequest\CollectionFactory $collectionFactory
    ) {
        $this->factory = $factory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Add new request and returns it.
     *
     * Notice:
     *  Existing request will be returned if the same with confirmed status found.
     *  This is done, to prevent duplicated jobs in the cron script.
     *
     * @param  array $data
     * @return \Swissup\Gdpr\Model\ClientRequest
     */
    public function add(array $data)
    {
        if (empty($data['client_identity_field'])) {
            $data['client_identity_field'] = 'email';
        }

        // 1. Search for the same request with confirmed status
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter('client_identity_field', $data['client_identity_field'])
            ->addFieldToFilter('client_identity', $data['client_identity'])
            ->addFieldToFilter('type', $data['type'])
            ->addFieldToFilter('status', [
                ClientRequest::STATUS_CONFIRMED,
                ClientRequest::STATUS_RUNNING,
            ]);

        if ($collection->getSize()) {
            return $collection->getFirstItem();
        }

        // 2. Create new request
        $request = $this->factory->create();
        $request->addData($data);
        $request->save();

        return $request;
    }
}
