<?php

namespace Swissup\Pagespeed\Plugin\View\Url;

use Swissup\Pagespeed\Helper\Config as ConfigHelper;

class CssResolver
{
    /**
     *
     * @var \Swissup\Pagespeed\Model\Css\Improver
     */
    private $cssImprover;

    /**
     * @param \Swissup\Pagespeed\Model\Css\Improver $cssImprover
     */
    public function __construct(
        \Swissup\Pagespeed\Model\Css\Improver $cssImprover
    ) {
        $this->cssImprover = $cssImprover;
    }

    /**
     *
     * @param \Magento\Framework\View\Url\CssResolver $subject
     * @param string $result
     * @return string
     */
    public function afterRelocateRelativeUrls(
        \Magento\Framework\View\Url\CssResolver $subject,
        $result
    ) {
        return $this->cssImprover->process($result);
    }
}
