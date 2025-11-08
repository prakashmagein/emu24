<?php

namespace Swissup\Gdpr\Model\PersonalDataHandler;

use Swissup\Gdpr\Model\ClientRequest;

class Quote extends AbstractHandler implements HandlerInterface
{
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Address\CollectionFactory
     */
    private $addressCollectionFactory;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Payment\CollectionFactory
     */
    private $paymentCollectionFactory;

    /**
     * @param \Swissup\Gdpr\Model\PersonalDataHandler\Context $context
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory
     * @param \Magento\Quote\Model\ResourceModel\Quote\Address\CollectionFactory $addressCollectionFactory
     * @param \Magento\Quote\Model\ResourceModel\Quote\Payment\CollectionFactory $paymentCollectionFactory
     */
    public function __construct(
        \Swissup\Gdpr\Model\PersonalDataHandler\Context $context,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
        \Magento\Quote\Model\ResourceModel\Quote\Address\CollectionFactory $addressCollectionFactory,
        \Magento\Quote\Model\ResourceModel\Quote\Payment\CollectionFactory $paymentCollectionFactory
    ) {
        parent::__construct($context);
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->addressCollectionFactory = $addressCollectionFactory;
        $this->paymentCollectionFactory = $paymentCollectionFactory;
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
        $quotes = $this->getQuoteCollection($request);

        if ($size = $quotes->getSize()) {
            $quoteIds = $quotes->getColumnValues('entity_id');
            $collections = [
                $this->addressCollectionFactory->create()->addFieldToFilter('quote_id', $quoteIds),
                $this->paymentCollectionFactory->create()->addFieldToFilter('quote_id', $quoteIds),
                $quotes,
            ];

            $this->anonymizeCollections(
                $collections,
                array_merge(
                    $this->faker->getAddressData($request),
                    $this->faker->getQuoteData($request),
                    $this->faker->getPaymentData($request)
                )
            );
        }

        $request->addSuccess(sprintf(
            'Quote data anonymization finished. %s carts were processed.',
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
     * @return \Magento\Quote\Model\ResourceModel\Quote\Collection
     */
    private function getQuoteCollection(ClientRequest $request)
    {
        $columns = ['customer_email'];
        $values = [$request->getClientIdentity()];
        if ($request->getCustomerId()) {
            $columns[] = 'customer_id';
            $values[] = $request->getCustomerId();
        }

        $collection = $this->quoteCollectionFactory->create()
            ->addFieldToFilter($columns, $values);

        if ($this->useWebsiteFilter()) {
            $collection->addFieldToFilter(
                'store_id',
                [
                    'or' => [
                        ['in' => $this->getStoreIds($request)],
                        ['null' => true],
                    ]
                ]
            );
        }

        return $collection;
    }
}
