<?php

namespace Swissup\Amp\Plugin\Swissup\ThemeEditor;

class HelperConfig
{
    /**
     * @var \Swissup\Amp\Helper\Data
     */
    private $helper;

    /**
     * @param \Swissup\Amp\Helper\Data $helper
     */
    public function __construct(
        \Swissup\Amp\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Swissup\ThemeEditor\Helper\Data $subject
     * @param boolean $result
     * @return boolean
     * @deprecated and no longer used. See HeaderConfig
     */
    public function afterIsHeaderEnabled(
        \Swissup\ThemeEditor\Helper\Data $subject,
        $result
    ) {
        if ($result && $this->helper->canUseAmp()) {
            $result = false;
        }
        return $result;
    }
}
