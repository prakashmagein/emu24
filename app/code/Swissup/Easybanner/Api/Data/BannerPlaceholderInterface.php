<?php
namespace Swissup\Easybanner\Api\Data;

interface BannerPlaceholderInterface
{
    const BANNER_ID = 'banner_id';
    const PLACEHOLDER_ID = 'placeholder_id';

    /**
     * Get banner_id
     *
     * return int
     */
    public function getBannerId();

    /**
     * Get placeholder_id
     *
     * return int
     */
    public function getPlaceholderId();

    /**
     * Set banner_id
     *
     * @param int $bannerId
     * return \Swissup\Easybanner\Api\Data\BannerPlaceholderInterface
     */
    public function setBannerId($bannerId);

    /**
     * Set placeholder_id
     *
     * @param int $placeholderId
     * return \Swissup\Easybanner\Api\Data\BannerPlaceholderInterface
     */
    public function setPlaceholderId($placeholderId);
}
