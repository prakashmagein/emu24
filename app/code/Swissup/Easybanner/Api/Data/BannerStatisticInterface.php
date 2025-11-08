<?php
namespace Swissup\Easybanner\Api\Data;

interface BannerStatisticInterface
{
    const ID = 'id';
    const BANNER_ID = 'banner_id';
    const DATE = 'date';
    const DISPLAY_COUNT = 'display_count';
    const CLICKS_COUNT = 'clicks_count';

    /**
     * Get id
     *
     * return int
     */
    public function getId();

    /**
     * Get banner_id
     *
     * return int
     */
    public function getBannerId();

    /**
     * Get date
     *
     * return string
     */
    public function getDate();

    /**
     * Get display_count
     *
     * return int
     */
    public function getDisplayCount();

    /**
     * Get clicks_count
     *
     * return int
     */
    public function getClicksCount();


    /**
     * Set id
     *
     * @param int $id
     * return \Swissup\Easybanner\Api\Data\BannerStatisticInterface
     */
    public function setId($id);

    /**
     * Set banner_id
     *
     * @param int $bannerId
     * return \Swissup\Easybanner\Api\Data\BannerStatisticInterface
     */
    public function setBannerId($bannerId);

    /**
     * Set date
     *
     * @param string $date
     * return \Swissup\Easybanner\Api\Data\BannerStatisticInterface
     */
    public function setDate($date);

    /**
     * Set display_count
     *
     * @param int $displayCount
     * return \Swissup\Easybanner\Api\Data\BannerStatisticInterface
     */
    public function setDisplayCount($displayCount);

    /**
     * Set clicks_count
     *
     * @param int $clicksCount
     * return \Swissup\Easybanner\Api\Data\BannerStatisticInterface
     */
    public function setClicksCount($clicksCount);
}
