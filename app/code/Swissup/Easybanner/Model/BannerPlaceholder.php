<?php
namespace Swissup\Easybanner\Model;

use Swissup\Easybanner\Api\Data\BannerPlaceholderInterface;
use Magento\Framework\DataObject\IdentityInterface;

class BannerPlaceholder extends \Magento\Framework\Model\AbstractModel
    implements BannerPlaceholderInterface, IdentityInterface
{
    /**
     * cache tag
     */
    const CACHE_TAG = 'banner_placeholder';

    /**
     * @var string
     */
    protected $_cacheTag = 'banner_placeholder';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'banner_placeholder';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Swissup\Easybanner\Model\ResourceModel\BannerPlaceholder');
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
     * Get placeholder_id
     *
     * return int
     */
    public function getPlaceholderId()
    {
        return $this->getData(self::PLACEHOLDER_ID);
    }

    /**
     * Set banner_id
     *
     * @param int $bannerId
     * return \Swissup\Easybanner\Api\Data\BannerPlaceholderInterface
     */
    public function setBannerId($bannerId)
    {
        return $this->setData(self::BANNER_ID, $bannerId);
    }

    /**
     * Set placeholder_id
     *
     * @param int $placeholderId
     * return \Swissup\Easybanner\Api\Data\BannerPlaceholderInterface
     */
    public function setPlaceholderId($placeholderId)
    {
        return $this->setData(self::PLACEHOLDER_ID, $placeholderId);
    }
}
