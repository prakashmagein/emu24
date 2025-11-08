<?php

namespace Swissup\Gdpr\Model\PersonalDataHandler;

class Context
{
    /**
     * @var \Swissup\Gdpr\Helper\Data
     */
    private $helper;

    /**
     * @var \Swissup\Gdpr\Model\Faker
     */
    private $faker;

    /**
     * @var \Magento\Customer\Model\Config\Share
     */
    private $shareConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Swissup\Gdpr\Helper\Data $helper
     * @param \Swissup\Gdpr\Model\Faker $faker
     * @param \Magento\Customer\Model\Config\Share $shareConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Swissup\Gdpr\Helper\Data $helper,
        \Swissup\Gdpr\Model\Faker $faker,
        \Magento\Customer\Model\Config\Share $shareConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->helper = $helper;
        $this->faker = $faker;
        $this->shareConfig = $shareConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @return \Swissup\Gdpr\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * @return \Swissup\Gdpr\Model\Faker
     */
    public function getFaker()
    {
        return $this->faker;
    }

    /**
     * @return \Magento\Customer\Model\Config\Share
     */
    public function getShareConfig()
    {
        return $this->shareConfig;
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }
}
