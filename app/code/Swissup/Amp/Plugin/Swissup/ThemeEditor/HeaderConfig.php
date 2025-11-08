<?php

namespace Swissup\Amp\Plugin\Swissup\ThemeEditor;

class HeaderConfig
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
     * @param \Swissup\ThemeEditor\Helper\Header $subject
     * @param boolean $result
     * @return boolean
     */
    public function afterIsHeaderEnabled(
        \Swissup\ThemeEditor\Helper\Header $subject,
        $result
    ) {
        if ($result && $this->helper->canUseAmp()) {
            $result = false;
        }
        return $result;
    }
}
