<?php

namespace Swissup\GdprTestimonials\Model;

use Swissup\Gdpr\Model\ClientRequest;
use Swissup\Gdpr\Model\PersonalDataHandler\AbstractHandler;
use Swissup\Gdpr\Model\PersonalDataHandler\HandlerInterface;
use Magento\Framework\Exception\LocalizedException;

class TestimonialDataHandler extends AbstractHandler implements HandlerInterface
{
    /**
     * @var \Swissup\Testimonials\Model\ResourceModel\Data\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param \Swissup\Gdpr\Model\PersonalDataHandler\Context $context
     * @param \Swissup\Testimonials\Model\ResourceModel\Data\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Swissup\Gdpr\Model\PersonalDataHandler\Context $context,
        \Swissup\Testimonials\Model\ResourceModel\Data\CollectionFactory $collectionFactory
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
                'name'     => $this->faker->getStaticPlaceholder(),
                'email'    => $this->faker->getEmail($request),
                'company'  => null,
                'website'  => null,
                'twitter'  => null,
                'facebook' => null,
                'image'    => null,
            ]
        );

        $request->addSuccess(sprintf(
            'Testimonials data anonymization finished. %s items where processed',
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
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('email', $request->getClientIdentity());

        if ($this->useWebsiteFilter()) {
            $collection->addStoreFilter($this->getStoreIds($request));
        }

        return $collection;
    }
}
