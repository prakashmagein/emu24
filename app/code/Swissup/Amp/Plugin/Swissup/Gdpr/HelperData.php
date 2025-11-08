<?php
namespace Swissup\Amp\Plugin\Swissup\Gdpr;

class HelperData
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
     * Disable GDPR
     *
     * @param  \Swissup\Gdpr\Helper\Data $subject
     * @return bool
     */
    public function afterIsGdprEnabled(
        \Swissup\Gdpr\Helper\Data $subject,
        $result
    ) {
        if ($this->helper->canUseAmp()) {
            return false;
        }

        return $result;
    }
}
