<?php
namespace Swissup\Amp\Plugin\Framework;

class ViewDesignExceptions
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
     * Disable user-agent design exceptions on AMP
     *
     * @param  \Magento\Framework\View\DesignExceptions $subject
     * @param  string|bool $result
     * @return string|bool
     */
    public function afterGetThemeByRequest(
        \Magento\Framework\View\DesignExceptions $subject,
        $result
    ) {
        if ($this->helper->canUseAmp()) {
            return false;
        }

        return $result;
    }
}
