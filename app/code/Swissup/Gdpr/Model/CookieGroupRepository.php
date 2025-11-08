<?php

namespace Swissup\Gdpr\Model;

class CookieGroupRepository
{
    /**
     * @var array
     */
    private $listWithCookies;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Swissup\Gdpr\Model\ResourceModel\Cookie\MergedCollectionFactory
     */
    private $cookieCollectionFactory;

    /**
     * @var \Swissup\Gdpr\Model\ResourceModel\CookieGroup\MergedCollectionFactory
     */
    private $cookieGroupCollectionFactory;

    /**
     * @param \Swissup\Gdpr\Model\ResourceModel\Cookie\MergedCollectionFactory $cookieCollectionFactory
     * @param \Swissup\Gdpr\Model\ResourceModel\CookieGroup\MergedCollectionFactory $cookieGroupCollectionFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Swissup\Gdpr\Model\ResourceModel\Cookie\MergedCollectionFactory $cookieCollectionFactory,
        \Swissup\Gdpr\Model\ResourceModel\CookieGroup\MergedCollectionFactory $cookieGroupCollectionFactory
    ) {
        $this->storeManager = $storeManager;
        $this->cookieCollectionFactory = $cookieCollectionFactory;
        $this->cookieGroupCollectionFactory = $cookieGroupCollectionFactory;
    }

    /**
     * @return \Swissup\Gdpr\Model\ResourceModel\CookieGroup\MergedCollection
     */
    public function getList()
    {
        return $this->cookieGroupCollectionFactory->create();
    }

    /**
     * @return array
     */
    public function getListWithCookies()
    {
        if ($this->listWithCookies !== null) {
            return $this->listWithCookies;
        }

        $storeId = $this->storeManager->getStore()->getId();
        $cookies = $this->cookieCollectionFactory->create()
            ->addFieldToFilter('status', 1)
            ->setStoreId($storeId);
        $groups = $this->cookieGroupCollectionFactory->create()
            ->setStoreId($storeId);

        $result = [];
        foreach ($groups as $group) {
            $code = $group->getCode();
            $groupCookies = $cookies->getItemsByColumnValue('group', $code);

            if (!$groupCookies) {
                continue;
            }

            $group->setCookies($groupCookies);
            $result[] = $group;
        }

        $this->listWithCookies = $result;

        return $result;
    }
}
