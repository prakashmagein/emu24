<?php namespace Swissup\EasySlide\Model;

use Swissup\EasySlide\Api\Data\SlidesInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Slides extends \Magento\Framework\Model\AbstractModel
    implements SlidesInterface, IdentityInterface
{
    /**
     * cache tag
     */
    const CACHE_TAG = 'easyslide_slides';

    /**
     * @var string
     */
    protected $_cacheTag = 'easyslide_slides';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'easyslide_slides';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Swissup\EasySlide\Model\ResourceModel\Slides');
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

    public function getSlides($sliderId)
    {
        return $this->_getResource()->getSlides($sliderId);
    }

    /**
     * Get slide_id
     *
     * return int
     */
    public function getSlideId()
    {
        return $this->getData(self::SLIDE_ID);
    }

    /**
     * Get slider_id
     *
     * return int
     */
    public function getSliderId()
    {
        return $this->getData(self::SLIDER_ID);
    }

    /**
     * Get title
     *
     * return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Get image
     *
     * return string
     */
    public function getImage()
    {
        return $this->getData(self::IMAGE);
    }

    /**
     * Get description
     *
     * return string
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * Get desc_position
     *
     * return string
     */
    public function getDescPosition()
    {
        return $this->getData(self::DESC_POSITION);
    }

    /**
     * Get desc_background
     *
     * return string
     */
    public function getDescBackground()
    {
        return $this->getData(self::DESC_BACKGROUND);
    }

    /**
     * Get url
     *
     * return string
     */
    public function getUrl()
    {
        return $this->getData(self::URL);
    }

    /**
     * Get target
     *
     * return string
     */
    public function getTarget()
    {
        return $this->getData(self::TARGET);
    }

    /**
     * Get sort_order
     *
     * return int
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * Get is_active
     *
     * return int
     */
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * Set slide_id
     *
     * @param int $slideId
     * return \Swissup\Easyslide\Api\Data\SlidesInterface
     */
    public function setSlideId($slideId)
    {
        return $this->setData(self::SLIDE_ID, $slideId);
    }

    /**
     * Set slider_id
     *
     * @param int $sliderId
     * return \Swissup\Easyslide\Api\Data\SlidesInterface
     */
    public function setSliderId($sliderId)
    {
        return $this->setData(self::SLIDER_ID, $sliderId);
    }

    /**
     * Set title
     *
     * @param string $title
     * return \Swissup\Easyslide\Api\Data\SlidesInterface
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Set image
     *
     * @param string $image
     * return \Swissup\Easyslide\Api\Data\SlidesInterface
     */
    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
    }

    /**
     * Set description
     *
     * @param string $description
     * return \Swissup\Easyslide\Api\Data\SlidesInterface
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Set desc_position
     *
     * @param string $descPosition
     * return \Swissup\Easyslide\Api\Data\SlidesInterface
     */
    public function setDescPosition($descPosition)
    {
        return $this->setData(self::DESC_POSITION, $descPosition);
    }

    /**
     * Set desc_background
     *
     * @param string $descBackground
     * return \Swissup\Easyslide\Api\Data\SlidesInterface
     */
    public function setDescBackground($descBackground)
    {
        return $this->setData(self::DESC_BACKGROUND, $descBackground);
    }

    /**
     * Set url
     *
     * @param string $url
     * return \Swissup\Easyslide\Api\Data\SlidesInterface
     */
    public function setUrl($url)
    {
        return $this->setData(self::URL, $url);
    }

    /**
     * Set target
     *
     * @param string $target
     * return \Swissup\Easyslide\Api\Data\SlidesInterface
     */
    public function setTarget($target)
    {
        return $this->setData(self::TARGET, $target);
    }

    /**
     * Set sort_order
     *
     * @param int $sortOrder
     * return \Swissup\Easyslide\Api\Data\SlidesInterface
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * Set is_active
     *
     * @param int $isActive
     * return \Swissup\Easyslide\Api\Data\SlidesInterface
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    public function isRawHtml(): bool
    {
        return !$this->getImage();
    }
}
