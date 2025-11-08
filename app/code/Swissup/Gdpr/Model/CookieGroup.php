<?php

namespace Swissup\Gdpr\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;

class CookieGroup extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'gdpr_c';

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
        $this->_init(\Swissup\Gdpr\Model\ResourceModel\CookieGroup::class);
    }

    /**
     * Merge default and store_view data
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
    public function getIdentities()
    {
        return [
            self::CACHE_TAG,
        ];
    }
}
