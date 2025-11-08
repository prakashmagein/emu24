<?php
namespace Swissup\Amp\Plugin\Framework;

class AppConfig
{
    private const XML_PATH_DEV_MOVE_JS_TO_BOTTOM = 'dev/js/move_script_to_bottom';

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
     * Retrieve config flag
     *
     * @param \Magento\Framework\App\Config $subject
     * @param bool $result
     * @param string $path
     * @return bool
     */
    public function afterIsSetFlag(
        \Magento\Framework\App\Config $subject, $result, $path
    ) {
        if ($this->helper->canUseAmp() &&
            $path === self::XML_PATH_DEV_MOVE_JS_TO_BOTTOM
        ) {
            return false;
        }

        return $result;
    }
}
