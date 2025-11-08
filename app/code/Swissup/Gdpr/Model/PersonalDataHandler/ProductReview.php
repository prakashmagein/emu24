<?php

namespace Swissup\Gdpr\Model\PersonalDataHandler;

use Swissup\Gdpr\Model\ClientRequest;

class ProductReview extends AbstractHandler implements HandlerInterface
{
    /**
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    private $reviewCollectionFactory;

    /**
     * @param \Swissup\Gdpr\Model\PersonalDataHandler\Context $context
     * @param \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory
     */
    public function __construct(
        \Swissup\Gdpr\Model\PersonalDataHandler\Context $context,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory
    ) {
        parent::__construct($context);
        $this->reviewCollectionFactory = $reviewCollectionFactory;
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
        if (!$customerId = $request->getCustomerId()) {
            return;
        }

        $collection = $this->reviewCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customerId);
        $size = $collection->getSize();

        $this->anonymizeCollections([
            $collection,
        ], [
            'nickname' => $this->faker->getStaticPlaceholder(),
        ]);

        $request->addSuccess(sprintf(
            'Product review data anonymization finished. %s reviews were processed.',
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
}
