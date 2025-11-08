<?php

namespace Swissup\Gdpr\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;

class Cookie extends AbstractModel implements IdentityInterface
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * @var integer
     */
    protected $storeId = 0;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Swissup\Gdpr\Model\ResourceModel\Cookie::class);
    }

    public function getNames()
    {
        return explode(',', $this->getName());
    }

    /**
     * Merge default and store_view data into $cookie
     *
     * @param  array $default
     * @param  array $scope
     * @return $this
     */
    public function addContentData($default, $scope)
    {
        $this->addData(array_merge(
            $default,
            array_filter($scope, function($value) {
                return $value !== null;
            })
        ));

        return $this;
    }

    /**
     * Get Store Id
     *
     * @return integer
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * Set store Id
     *
     * @param integer $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ENABLED => __('Enabled'),
            self::STATUS_DISABLED => __('Disabled'),
        ];
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [
            CookieGroup::CACHE_TAG,
        ];
    }
}
