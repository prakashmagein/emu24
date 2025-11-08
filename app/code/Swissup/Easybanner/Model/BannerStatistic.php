<?php
namespace Swissup\Easybanner\Model;

use Swissup\Easybanner\Api\Data\BannerStatisticInterface;
use Magento\Framework\DataObject\IdentityInterface;

class BannerStatistic extends \Magento\Framework\Model\AbstractModel
    implements BannerStatisticInterface, IdentityInterface
{
    /**
     * cache tag
     */
    const CACHE_TAG = 'banner_statistic';

    /**
     * @var string
     */
    protected $_cacheTag = 'banner_statistic';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'banner_statistic';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Swissup\Easybanner\Model\ResourceModel\BannerStatistic');
    }

    public function incrementDisplayCount($bannerId)
    {
        return $this->_getResource()->incrementDisplayCount($bannerId);
    }

    public function incrementClicksCount($bannerId)
    {
        return $this->_getResource()->incrementClicksCount($bannerId);
    }

    public function getChartStatisticData($bannerId, $type)
    {
        return $this->_getResource()->getChartStatisticData($bannerId, $type);
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
     * Get id
     *
     * return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
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
     * Get date
     *
     * return string
     */
    public function getDate()
    {
        return $this->getData(self::DATE);
    }

    /**
     * Get display_count
     *
     * return int
     */
    public function getDisplayCount()
    {
        return $this->getData(self::DISPLAY_COUNT);
    }

    /**
     * Get clicks_count
     *
     * return int
     */
    public function getClicksCount()
    {
        return $this->getData(self::CLICKS_COUNT);
    }

    /**
     * Set id
     *
     * @param int $id
     * return \Swissup\Easybanner\Api\Data\BannerStatisticInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Set banner_id
     *
     * @param int $bannerId
     * return \Swissup\Easybanner\Api\Data\BannerStatisticInterface
     */
    public function setBannerId($bannerId)
    {
        return $this->setData(self::BANNER_ID, $bannerId);
    }

    /**
     * Set date
     *
     * @param string $date
     * return \Swissup\Easybanner\Api\Data\BannerStatisticInterface
     */
    public function setDate($date)
    {
        return $this->setData(self::DATE, $date);
    }

    /**
     * Set display_count
     *
     * @param int $displayCount
     * return \Swissup\Easybanner\Api\Data\BannerStatisticInterface
     */
    public function setDisplayCount($displayCount)
    {
        return $this->setData(self::DISPLAY_COUNT, $displayCount);
    }

    /**
     * Set clicks_count
     *
     * @param int $clicksCount
     * return \Swissup\Easybanner\Api\Data\BannerStatisticInterface
     */
    public function setClicksCount($clicksCount)
    {
        return $this->setData(self::CLICKS_COUNT, $clicksCount);
    }
}
