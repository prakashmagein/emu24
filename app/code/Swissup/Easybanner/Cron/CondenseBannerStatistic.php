<?php

namespace Swissup\Easybanner\Cron;

/**
 * Easy banners cron action
 */
class CondenseBannerStatistic
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Swissup\Easybanner\Model\ResourceModel\BannerStatistic
     */
    protected $bannerStatisticResource;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Swissup\Easybanner\Model\ResourceModel\BannerStatistic $bannerStatisticResource
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Swissup\Easybanner\Model\ResourceModel\BannerStatistic $bannerStatisticResource
    ) {
        $this->date = $date;
        $this->bannerStatisticResource = $bannerStatisticResource;
    }

    /**
     * Condense banner statistics in table
     *
     * @return $this
     */
    public function execute()
    {
        // condense data for yesterday
        $yesterday = $this->date->gmtDate('Y-m-d', '-1 days');
        $this->bannerStatisticResource->condenseStatistic($yesterday);
        return $this;
    }
}
