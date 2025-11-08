<?php

namespace Swissup\Gdpr\Model;

class BlockedCookieRepository
{
    /**
     * @var \Swissup\Gdpr\Model\ResourceModel\BlockedCookie\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param \Swissup\Gdpr\Model\ResourceModel\BlockedCookie\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Swissup\Gdpr\Model\ResourceModel\BlockedCookie\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param array $data
     * @return void
     */
    public function registerCookie(array $data)
    {
        if (empty($data['name'])) {
            return;
        }

        $cookie = $this->collectionFactory->create()
            ->addFieldToFilter('name', $data['name'])
            ->getFirstItem();

        if ($cookie->getId()) {
            return;
        }

        $cookie->setData($data)->save();
    }
}
