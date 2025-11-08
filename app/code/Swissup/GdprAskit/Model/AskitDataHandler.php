<?php

namespace Swissup\GdprAskit\Model;

use Swissup\Gdpr\Model\ClientRequest;
use Swissup\Gdpr\Model\PersonalDataHandler\AbstractHandler;
use Swissup\Gdpr\Model\PersonalDataHandler\HandlerInterface;
use Magento\Framework\Exception\LocalizedException;

class AskitDataHandler extends AbstractHandler implements HandlerInterface
{
    /**
     * @var \Swissup\Askit\Model\ResourceModel\Message\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param \Swissup\Gdpr\Model\PersonalDataHandler\Context $context
     * @param \Swissup\Askit\Model\ResourceModel\Message\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Swissup\Gdpr\Model\PersonalDataHandler\Context $context,
        \Swissup\Askit\Model\ResourceModel\Message\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
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
        $items = $this->getCollection($request);
        $size = $items->getSize();

        $this->anonymizeCollections(
            [
                $items
            ],
            [
                'customer_name' => $this->faker->getStaticPlaceholder(),
                'email' => $this->faker->getEmail($request),
            ]
        );

        $request->addSuccess(sprintf(
            'Askit data anonymization finished. %s items where processed',
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
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    private function getCollection(ClientRequest $request)
    {
        $columns = ['email'];
        $values = [$request->getClientIdentity()];
        if ($request->getCustomerId()) {
            $columns[] = 'customer_id';
            $values[] = $request->getCustomerId();
        }

        $collection = $this->collectionFactory->create()
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
