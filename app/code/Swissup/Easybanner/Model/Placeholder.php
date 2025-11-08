<?php
namespace Swissup\Easybanner\Model;

use Swissup\Easybanner\Api\Data\PlaceholderInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Placeholder extends \Magento\Framework\Model\AbstractModel
    implements PlaceholderInterface, IdentityInterface
{
    /**
     * cache tag
     */
    const CACHE_TAG = 'placeholder_';

    /**
     * @var string
     */
    protected $_cacheTag = 'placeholder_';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'placeholder_';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Swissup\Easybanner\Model\ResourceModel\Placeholder');
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
     * Get placeholder_id
     *
     * return int
     */
    public function getPlaceholderId()
    {
        return $this->getData(self::PLACEHOLDER_ID);
    }

    /**
     * Get name
     *
     * return string
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Get parent_block
     *
     * return string
     */
    public function getParentBlock()
    {
        return $this->getData(self::PARENT_BLOCK);
    }

    /**
     * Get position
     *
     * return string
     */
    public function getPosition()
    {
        return $this->getData(self::POSITION);
    }

    /**
     * Get status
     *
     * return int
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Get limit
     *
     * return int
     */
    public function getLimit()
    {
        return $this->getData(self::LIMIT);
    }

    /**
     * Get mode
     *
     * return string
     */
    public function getMode()
    {
        return $this->getData(self::MODE);
    }

    /**
     * Get banner_offset
     *
     * return int
     */
    public function getBannerOffset()
    {
        return $this->getData(self::BANNER_OFFSET);
    }

    /**
     * Get sort_mode
     *
     * return string
     */
    public function getSortMode()
    {
        return $this->getData(self::SORT_MODE);
    }

    /**
     * Set placeholder_id
     *
     * @param int $placeholderId
     * return \Swissup\Easybanner\Api\Data\PlaceholderInterface
     */
    public function setPlaceholderId($placeholderId)
    {
        return $this->setData(self::PLACEHOLDER_ID, $placeholderId);
    }

    /**
     * Set name
     *
     * @param string $name
     * return \Swissup\Easybanner\Api\Data\PlaceholderInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Set parent_block
     *
     * @param string $parentBlock
     * return \Swissup\Easybanner\Api\Data\PlaceholderInterface
     */
    public function setParentBlock($parentBlock)
    {
        return $this->setData(self::PARENT_BLOCK, $parentBlock);
    }

    /**
     * Set position
     *
     * @param string $position
     * return \Swissup\Easybanner\Api\Data\PlaceholderInterface
     */
    public function setPosition($position)
    {
        return $this->setData(self::POSITION, $position);
    }

    /**
     * Set status
     *
     * @param int $status
     * return \Swissup\Easybanner\Api\Data\PlaceholderInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Set limit
     *
     * @param int $limit
     * return \Swissup\Easybanner\Api\Data\PlaceholderInterface
     */
    public function setLimit($limit)
    {
        return $this->setData(self::LIMIT, $limit);
    }

    /**
     * Set mode
     *
     * @param string $mode
     * return \Swissup\Easybanner\Api\Data\PlaceholderInterface
     */
    public function setMode($mode)
    {
        return $this->setData(self::MODE, $mode);
    }

    /**
     * Set banner_offset
     *
     * @param int $bannerOffset
     * return \Swissup\Easybanner\Api\Data\PlaceholderInterface
     */
    public function setBannerOffset($bannerOffset)
    {
        return $this->setData(self::BANNER_OFFSET, $bannerOffset);
    }

    /**
     * Set sort_mode
     *
     * @param string $sortMode
     * return \Swissup\Easybanner\Api\Data\PlaceholderInterface
     */
    public function setSortMode($sortMode)
    {
        return $this->setData(self::SORT_MODE, $sortMode);
    }

    /**
     * Process object after save;
     * save banner offset for placeholder
     *
     * @return $this
     */
    public function afterSave()
    {
        // save banner offset for placeholder
        $storeBannerOffset = isset($this->storedData['banner_offset'])
            ? $this->storedData['banner_offset'] : '0';

        if (null !== $this->getBannerOffset()
            && $storeBannerOffset != $this->getBannerOffset()) {

            $this->getResource()->saveBannerOffset(
                $this->getId(),
                $this->getBannerOffset()
            );
        }

        return parent::afterSave();
    }

    /**
     * Processing object after load data
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $bannerOffset = $this->getResource()->getBannerOffset($this->getId());
        $this->setBannerOffset($bannerOffset);
        return parent::_afterLoad();
    }
}
