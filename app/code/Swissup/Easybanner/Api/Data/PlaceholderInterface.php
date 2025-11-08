<?php
namespace Swissup\Easybanner\Api\Data;

interface PlaceholderInterface
{
    const PLACEHOLDER_ID = 'placeholder_id';
    const NAME = 'name';
    const PARENT_BLOCK = 'parent_block';
    const POSITION = 'position';
    const STATUS = 'status';
    const LIMIT = 'limit';
    const MODE = 'mode';
    const BANNER_OFFSET = 'banner_offset';
    const SORT_MODE = 'sort_mode';

    /**
     * Get placeholder_id
     *
     * return int
     */
    public function getPlaceholderId();

    /**
     * Get name
     *
     * return string
     */
    public function getName();

    /**
     * Get parent_block
     *
     * return string
     */
    public function getParentBlock();

    /**
     * Get position
     *
     * return string
     */
    public function getPosition();

    /**
     * Get status
     *
     * return int
     */
    public function getStatus();

    /**
     * Get limit
     *
     * return int
     */
    public function getLimit();

    /**
     * Get mode
     *
     * return string
     */
    public function getMode();

    /**
     * Get banner_offset
     *
     * return int
     */
    public function getBannerOffset();

    /**
     * Get sort_mode
     *
     * return string
     */
    public function getSortMode();

    /**
     * Set placeholder_id
     *
     * @param int $placeholderId
     * return \Swissup\Easybanner\Api\Data\PlaceholderInterface
     */
    public function setPlaceholderId($placeholderId);

    /**
     * Set name
     *
     * @param string $name
     * return \Swissup\Easybanner\Api\Data\PlaceholderInterface
     */
    public function setName($name);

    /**
     * Set parent_block
     *
     * @param string $parentBlock
     * return \Swissup\Easybanner\Api\Data\PlaceholderInterface
     */
    public function setParentBlock($parentBlock);

    /**
     * Set position
     *
     * @param string $position
     * return \Swissup\Easybanner\Api\Data\PlaceholderInterface
     */
    public function setPosition($position);

    /**
     * Set status
     *
     * @param int $status
     * return \Swissup\Easybanner\Api\Data\PlaceholderInterface
     */
    public function setStatus($status);

    /**
     * Set limit
     *
     * @param int $limit
     * return \Swissup\Easybanner\Api\Data\PlaceholderInterface
     */
    public function setLimit($limit);

    /**
     * Set mode
     *
     * @param string $mode
     * return \Swissup\Easybanner\Api\Data\PlaceholderInterface
     */
    public function setMode($mode);

    /**
     * Set banner_offset
     *
     * @param int $bannerOffset
     * return \Swissup\Easybanner\Api\Data\PlaceholderInterface
     */
    public function setBannerOffset($bannerOffset);

    /**
     * Set sort_mode
     *
     * @param string $sortMode
     * return \Swissup\Easybanner\Api\Data\PlaceholderInterface
     */
    public function setSortMode($sortMode);
}
