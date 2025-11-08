<?php
namespace Swissup\Amp\Helper;

class Device extends \Magento\Framework\App\Helper\AbstractHelper
{
    const TYPE_MOBILE   = 'mobile';
    const TYPE_TABLET   = 'tablet';
    const TYPE_DESKTOP  = 'desktop';

    protected $mobileDetect = null;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->mobileDetect = new \Detection\MobileDetect();

        parent::__construct($context);
    }

    /**
     * Detect device type using Mobile_Detect http://mobiledetect.net/
     * @return string
     */
    public function getDeviceType()
    {
        if ($this->mobileDetect) {
            if ($this->mobileDetect->isTablet()) {
                return self::TYPE_TABLET;
            } elseif ($this->mobileDetect->isMobile()) {
                return self::TYPE_MOBILE;
            }
        }

        return self::TYPE_DESKTOP;
    }
}
