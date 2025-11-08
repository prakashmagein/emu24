<?php
namespace Swissup\EasySlide\Api\Data;

interface SlidesInterface
{
    const SLIDE_ID = 'slide_id';
    const SLIDER_ID = 'slider_id';
    const TITLE = 'title';
    const IMAGE = 'image';
    const DESCRIPTION = 'description';
    const DESC_POSITION = 'desc_position';
    const DESC_BACKGROUND = 'desc_background';
    const URL = 'url';
    const TARGET = 'target';
    const SORT_ORDER = 'sort_order';
    const IS_ACTIVE = 'is_active';

    /**
     * Get slide_id
     *
     * return int
     */
    public function getSlideId();

    /**
     * Get slider_id
     *
     * return int
     */
    public function getSliderId();

    /**
     * Get slide title
     *
     * return string
     */
    public function getTitle();

    /**
     * Get image
     *
     * return string
     */
    public function getImage();

    /**
     * Get description
     *
     * return string
     */
    public function getDescription();

    /**
     * Get desc_position
     *
     * return string
     */
    public function getDescPosition();

    /**
     * Get desc_background
     *
     * return string
     */
    public function getDescBackground();

    /**
     * Get url
     *
     * return string
     */
    public function getUrl();

    /**
     * Get target
     *
     * return string
     */
    public function getTarget();

    /**
     * Get sort_order
     *
     * return int
     */
    public function getSortOrder();

    /**
     * Get is_active
     *
     * return int
     */
    public function getIsActive();

    /**
     * Set slide_id
     *
     * @param int $slideId
     * return \Swissup\Easyslide\Api\Data\SlidesInterface
     */
    public function setSlideId($slideId);

    /**
     * Set slider_id
     *
     * @param int $sliderId
     * return \Swissup\Easyslide\Api\Data\SlidesInterface
     */
    public function setSliderId($sliderId);

    /**
     * Set title
     *
     * @param string $title
     * return \Swissup\Easyslide\Api\Data\SlidesInterface
     */
    public function setTitle($title);

    /**
     * Set image
     *
     * @param string $image
     * return \Swissup\Easyslide\Api\Data\SlidesInterface
     */
    public function setImage($image);

    /**
     * Set description
     *
     * @param string $description
     * return \Swissup\Easyslide\Api\Data\SlidesInterface
     */
    public function setDescription($description);

    /**
     * Set desc_position
     *
     * @param string $descPosition
     * return \Swissup\Easyslide\Api\Data\SlidesInterface
     */
    public function setDescPosition($descPosition);

    /**
     * Set desc_background
     *
     * @param string $descBackground
     * return \Swissup\Easyslide\Api\Data\SlidesInterface
     */
    public function setDescBackground($descBackground);

    /**
     * Set url
     *
     * @param string $url
     * return \Swissup\Easyslide\Api\Data\SlidesInterface
     */
    public function setUrl($url);

    /**
     * Set target
     *
     * @param string $target
     * return \Swissup\Easyslide\Api\Data\SlidesInterface
     */
    public function setTarget($target);

    /**
     * Set sort_order
     *
     * @param int $sortOrder
     * return \Swissup\Easyslide\Api\Data\SlidesInterface
     */
    public function setSortOrder($sortOrder);

    /**
     * Set is_active
     *
     * @param int $isActive
     * return \Swissup\Easyslide\Api\Data\SlidesInterface
     */
    public function setIsActive($isActive);
}
