<?php

namespace Swissup\Easybanner\Cron;

/**
 * Easy banners cron action
 */
class CondenseBannerOffset
{

    /**
     * @var \Swissup\Easybanner\Model\ResourceModel\Placeholder
     */
    protected $placeholderResource;

    public function __construct(
        \Swissup\Easybanner\Model\ResourceModel\Placeholder $placeholderResource
    ) {
        $this->placeholderResource = $placeholderResource;
    }
    /**
     * Condense banner offset data for placeholders in table
     *
     * @return $this
     */
    public function execute()
    {
        $this->placeholderResource->condenseBannerOffsetData();
        return $this;
    }
}
