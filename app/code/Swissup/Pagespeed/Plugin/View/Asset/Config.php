<?php

namespace Swissup\Pagespeed\Plugin\View\Asset;

use Swissup\Pagespeed\Helper\Config as ConfigHelper;

class Config
{

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @param ConfigHelper $configHelper
     */
    public function __construct(ConfigHelper $configHelper)
    {
        $this->configHelper = $configHelper;
    }

    /**
     *
     * @param  \Magento\Framework\View\Asset\Config $subject
     * @return bool
     */
    public function afterIsMergeJsFiles(
        \Magento\Framework\View\Asset\Config $subject,
        $result
    ) {

        if ($result && $this->configHelper->isMergeJsFilesForMobileDisabled()) {
            return false;
        }

        return $result;
    }

    /**
     *
     * @param  \Magento\Framework\View\Asset\Config $subject
     * @return bool
     */
    public function afterIsMergeCssFiles(
        \Magento\Framework\View\Asset\Config $subject,
        $result
    ) {

        if ($result && $this->configHelper->isMergeCssFilesForMobileDisabled()) {
            return false;
        }

        return $result;
    }
}
