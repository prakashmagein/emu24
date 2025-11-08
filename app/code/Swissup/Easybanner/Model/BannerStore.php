<?php
namespace Swissup\Easybanner\Model;

use Swissup\Easybanner\Api\Data\BannerStoreInterface;
use Magento\Framework\DataObject\IdentityInterface;

class BannerStore extends \Magento\Framework\Model\AbstractModel
    implements BannerStoreInterface, IdentityInterface
{
    /**
     * cache tag
     */
    const CACHE_TAG = 'banner_store';

    /**
     * @var string
     */
    protected $_cacheTag = 'banner_store';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'banner_store';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Swissup\Easybanner\Model\ResourceModel\BannerStore');
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get banner_id
     *
     * return int
     */
    public function getBannerId()
    {
        return $this->getData(self::BANNER_ID);
    }

    /**
     * Get store_id
     *
     * return int
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * Set banner_id
     *
     * @param int $bannerId
     * return \Swissup\Easybanner\Api\Data\BannerStoreInterface
     */
    public function setBannerId($bannerId)
    {
        return $this->setData(self::BANNER_ID, $bannerId);
    }

    /**
     * Set store_id
     *
     * @param int $storeId
     * return \Swissup\Easybanner\Api\Data\BannerStoreInterface
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }
}
