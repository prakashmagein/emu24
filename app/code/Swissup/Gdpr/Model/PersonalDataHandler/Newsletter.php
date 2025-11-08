<?php

namespace Swissup\Gdpr\Model\PersonalDataHandler;

use Swissup\Gdpr\Model\ClientRequest;

class Newsletter extends AbstractHandler implements HandlerInterface
{
    /**
     * @var \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory
     */
    private $subscriberCollectionFactory;

    /**
     * @param \Swissup\Gdpr\Model\PersonalDataHandler\Context $context
     * @param \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subscriberCollectionFactory
     */
    public function __construct(
        \Swissup\Gdpr\Model\PersonalDataHandler\Context $context,
        \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subscriberCollectionFactory
    ) {
        parent::__construct($context);
        $this->subscriberCollectionFactory = $subscriberCollectionFactory;
    }

    /**
     * @return void
     */
    public function delete(ClientRequest $request)
    {
        $collection = $this->getSubscriberCollection($request);
        $size = $collection->getSize();
        foreach ($collection as $item) {
            $item->delete();
        }

        $request->addSuccess(sprintf(
            'Newsletter data deletion finished. %s subscribers were deleted.',
            $size
        ));
    }

    /**
     * @return void
     */
    public function anonymize(ClientRequest $request)
    {
        $this->delete($request);
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
     * @return \Magento\Newsletter\Model\ResourceModel\Subscriber\Collection
     */
    private function getSubscriberCollection(ClientRequest $request)
    {
        $columns = ['subscriber_email'];
        $values = [$request->getClientIdentity()];
        if ($request->getCustomerId()) {
            $columns[] = 'customer_id';
            $values[] = $request->getCustomerId();
        }

        $collection = $this->subscriberCollectionFactory->create()
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
