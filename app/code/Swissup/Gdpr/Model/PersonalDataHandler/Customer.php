<?php

namespace Swissup\Gdpr\Model\PersonalDataHandler;

use Swissup\Gdpr\Model\ClientRequest;

class Customer extends AbstractHandler implements HandlerInterface
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Address\CollectionFactory
     */
    private $addressCollectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Grid\CollectionFactory
     */
    private $addressGridCollectionFactory;

    /**
     * @param \Swissup\Gdpr\Model\PersonalDataHandler\Context $context
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     * @param \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $addressCollectionFactory
     * @param \Magento\Customer\Model\ResourceModel\Grid\CollectionFactory $addressGridCollectionFactory
     */
    public function __construct(
        \Swissup\Gdpr\Model\PersonalDataHandler\Context $context,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $addressCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Grid\CollectionFactory $addressGridCollectionFactory
    ) {
        parent::__construct($context);
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->addressCollectionFactory = $addressCollectionFactory;
        $this->addressGridCollectionFactory = $addressGridCollectionFactory;
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
        $collection = $this->getCustomerCollection($request);

        if ($size = $collection->getSize()) {
            $customerIds = $collection->getColumnValues('entity_id');
            $collections = [
                $this->addressCollectionFactory->create()->addFieldToFilter('parent_id', $customerIds),
                $this->addressGridCollectionFactory->create()->addFieldToFilter('entity_id', $customerIds),
                $collection,
            ];

            $this->anonymizeCollections(
                $collections,
                $this->faker->getCustomerData($request),
                true
            );
        }

        $request->addSuccess(sprintf(
            'Customer data anonymization finished. %s customer was processed.',
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
     * @param  ClientRequest $request
     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    private function getCustomerCollection(ClientRequest $request)
    {
        $filters = [
            [
                'attribute' => 'email',
                'eq' => $request->getClientIdentity()
            ]
        ];
        if ($request->getCustomerId()) {
            $filters[] = [
                'attribute' => 'entity_id',
                'eq' => $request->getCustomerId()
            ];
        }

        $collection = $this->customerCollectionFactory->create()
            ->addFieldToFilter($filters);

        if ($this->useWebsiteFilter()) {
            $collection->addFieldToFilter(
                'website_id',
                [
                    'or' => [
                        ['eq' => $request->getWebsiteId()],
                        ['null' => true],
                    ]
                ]
            );
        }

        return $collection;
    }
}
