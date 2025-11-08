<?php

namespace Swissup\Gdpr\Model\PersonalDataHandler;

use Swissup\Gdpr\Model\ClientRequest;
use Swissup\Gdpr\Model\ResourceModel\Client\AbstractCollection;

class Gdpr extends AbstractHandler implements HandlerInterface
{
    /**
     * @var \Swissup\Gdpr\Model\ResourceModel\ClientConsent\CollectionFactory
     */
    private $consentCollectionFactory;

    /**
     * @var \Swissup\Gdpr\Model\ResourceModel\ClientRequest\CollectionFactory
     */
    private $requestCollectionFactory;

    /**
     * @param \Swissup\Gdpr\Model\PersonalDataHandler\Context $context
     * @param \Swissup\Gdpr\Model\ResourceModel\ClientConsent\CollectionFactory $consentCollectionFactory
     * @param \Swissup\Gdpr\Model\ResourceModel\ClientRequest\CollectionFactory $requestCollectionFactory
     */
    public function __construct(
        \Swissup\Gdpr\Model\PersonalDataHandler\Context $context,
        \Swissup\Gdpr\Model\ResourceModel\ClientConsent\CollectionFactory $consentCollectionFactory,
        \Swissup\Gdpr\Model\ResourceModel\ClientRequest\CollectionFactory $requestCollectionFactory
    ) {
        parent::__construct($context);
        $this->consentCollectionFactory = $consentCollectionFactory;
        $this->requestCollectionFactory = $requestCollectionFactory;
    }

    /**
     * @return void
     */
    public function delete(ClientRequest $request)
    {
        $this->anonymize($request);
    }

    /**
     * @return void
     */
    public function anonymize(ClientRequest $request)
    {
        $consents = $this->applyCollectionFilters(
            $this->consentCollectionFactory->create(),
            $request
        );
        $requests = $this->applyCollectionFilters(
            $this->requestCollectionFactory->create(),
            $request
        );
        $size = $consents->getSize() + $requests->getSize();

        $this->anonymizeCollections([
            $consents,
            $requests,
        ], [
            'client_identity' => $this->faker->getEmail($request),
        ]);

        $request->addSuccess(sprintf(
            'GDPR data anonymization finished. %s items where processed',
            $size
        ));
    }

    /**
     * @return array
     */
    public function export(ClientRequest $request)
    {
        return [];
    }

    /**
     * @param  AbstractCollection $collection
     * @param  ClientRequest $collection
     * @return AbstractCollection
     */
    private function applyCollectionFilters(AbstractCollection $collection, ClientRequest $request)
    {
        $columns = ['client_identity'];
        $values = [$request->getClientIdentity()];
        if ($request->getCustomerId()) {
            $columns[] = 'customer_id';
            $values[] = $request->getCustomerId();
        }

        $collection->addFieldToFilter($columns, $values);

        if ($this->useWebsiteFilter()) {
            $collection->addFieldToFilter(
                'website_id',
                [
                    'or' => [
                        ['in' => [0, $request->getWebsiteId()]],
                        ['null' => true],
                    ]
                ]
            );
        }

        return $collection;
    }
}
