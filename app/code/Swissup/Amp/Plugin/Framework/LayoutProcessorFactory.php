<?php
namespace Swissup\Amp\Plugin\Framework;

class LayoutProcessorFactory
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
     * Add amp cache suffix
     *
     * @param \Magento\Framework\View\Layout\ProcessorFactory $subject
     * @param array $data
     * @return \Magento\Framework\View\Layout\ProcessorInterface
     */
    public function beforeCreate(
        \Magento\Framework\View\Layout\ProcessorFactory $subject,
        array $data = []
    ) {
        if ($this->helper->canUseAmp()) {
            if (isset($data['cacheSuffix'])) {
                $data['cacheSuffix'] .= '_amp';
            } else {
                $data['cacheSuffix'] = '_amp';
            }
        }

        return [$data];
    }
}
