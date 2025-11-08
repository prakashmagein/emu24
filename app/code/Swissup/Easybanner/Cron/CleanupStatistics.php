<?php

namespace Swissup\Easybanner\Cron;

use Magento\Framework\Stdlib\DateTime;

class CleanupStatistics
{
    /**
     * @var \Swissup\Easybanner\Model\ResourceModel\BannerStatisticFactory
     */
    private $statisticsFactory;

    /**
     * @param \Swissup\Easybanner\Model\ResourceModel\BannerStatisticFactory $statisticsFactory
     */
    public function __construct(
        \Swissup\Easybanner\Model\ResourceModel\BannerStatisticFactory $statisticsFactory
    ) {
        $this->statisticsFactory = $statisticsFactory;
    }

    public function execute()
    {
        $date = (new \DateTime('-6 months'))->format(DateTime::DATETIME_PHP_FORMAT);

        $this->statisticsFactory->create()->clear(['date < ?' => $date]);
    }
}
