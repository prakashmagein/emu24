<?php

namespace Swissup\GdprReviewreminder\Model;

use Swissup\Gdpr\Model\ClientRequest;
use Swissup\Gdpr\Model\PersonalDataHandler\AbstractHandler;
use Swissup\Gdpr\Model\PersonalDataHandler\HandlerInterface;
use Swissup\Reviewreminder\Model\Entity;
use Magento\Framework\Exception\LocalizedException;

class ReminderDataHandler extends AbstractHandler implements HandlerInterface
{
    /**
     * @var \Swissup\Reviewreminder\Model\ResourceModel\Entity\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param \Swissup\Gdpr\Model\PersonalDataHandler\Context $context
     * @param \Swissup\Reviewreminder\Model\ResourceModel\Entity\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Swissup\Gdpr\Model\PersonalDataHandler\Context $context,
        \Swissup\Reviewreminder\Model\ResourceModel\Entity\CollectionFactory $collectionFactory
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
                'customer_email' => $this->faker->getEmail($request),
                'status' => Entity::STATUS_CANCELLED,
            ]
        );

        $request->addSuccess(sprintf(
            'ReviewReminder data anonymization finished. %s items where processed',
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
     * @return \Swissup\Reviewreminder\Model\ResourceModel\Entity\Collection
     */
    private function getCollection(ClientRequest $request)
    {
        return $this->collectionFactory->create()
            ->addFieldToFilter('customer_email', $request->getClientIdentity());
    }
}
