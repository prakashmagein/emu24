<?php
namespace Swissup\Amp\Plugin\Swissup\Pagespeed;

class HelperConfig
{
    /**
     * @var \Swissup\Amp\Helper\Data
     */
    protected $helper;

    /**
     * @param \Swissup\Amp\Helper\Data $helper
     */
    public function __construct(
        \Swissup\Amp\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Disable Pagespeed
     *
     * @param  \Swissup\Pagespeed\Helper\Config $subject
     * @return bool
     */
    public function afterIsEnable(
        \Swissup\Pagespeed\Helper\Config $subject,
        $result
    ) {
        if ($this->helper->canUseAmp()) {
            return false;
        }

        return $result;
    }
}
