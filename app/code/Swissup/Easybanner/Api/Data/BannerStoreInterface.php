<?php
namespace Swissup\Easybanner\Api\Data;

interface BannerStoreInterface
{
    const BANNER_ID = 'banner_id';
    const STORE_ID = 'store_id';

    /**
     * Get banner_id
     *
     * return int
     */
    public function getBannerId();

    /**
     * Get store_id
     *
     * return int
     */
    public function getStoreId();


    /**
     * Set banner_id
     *
     * @param int $bannerId
     * return \Swissup\Easybanner\Api\Data\BannerStoreInterface
     */
    public function setBannerId($bannerId);

    /**
     * Set store_id
     *
     * @param int $storeId
     * return \Swissup\Easybanner\Api\Data\BannerStoreInterface
     */
    public function setStoreId($storeId);
}
