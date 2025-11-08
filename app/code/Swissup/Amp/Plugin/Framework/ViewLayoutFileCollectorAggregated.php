<?php
namespace Swissup\Amp\Plugin\Framework;

class ViewLayoutFileCollectorAggregated
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
     * Retrieve files
     *
     * Aggregate layout files from modules and a theme and its ancestors
     *
     * @param \Magento\Framework\View\Layout\File\Collector\Aggregated $subject
     * @param \Magento\Framework\View\File[] $result
     * @return \Magento\Framework\View\File[]
     */
    public function afterGetFiles(
        \Magento\Framework\View\Layout\File\Collector\Aggregated $subject,
        array $result
    ) {
        if (!$this->helper->canUseAmp()) {
            return $result;
        }

        $node = $this->helper->getConfig('swissup_amp/whitelist/layout_updates');
        $whitelist = array_values($node);
        foreach ($result as $key => $value) {
            if (!in_array($value->getModule(), $whitelist)) {
                unset($result[$key]);
            }

            if ($value->getTheme() && strpos($value->getFilename(), 'Swissup_Amp') === false) {
                if (!in_array($value->getTheme()->getCode(), ['Magento/blank', 'Magento/luma'])) {
                    unset($result[$key]);
                }
            }
        }

        return $result;
    }
}
