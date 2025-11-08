<?php
namespace Swissup\Amp\Observer;

use Magento\Framework\Event\ObserverInterface;

class ViewBlockAbstractToHtmlBefore implements ObserverInterface
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
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helper->canUseAmp()) return;

        $node = $this->helper->getConfig('swissup_amp/whitelist/block_types');
        $blocks = [];
        foreach ($node as $nodeName => $blockTypes) {
            $blocks = array_merge($blocks, array_values($blockTypes));
        }

        $blockClass = $this->helper->getCleanClass($observer->getBlock());
        $current = array_filter($blocks, function ($var) use ($blockClass) {
            return ($var['class'] == $blockClass);
        });

        $templateColumn = array_column($current, 'template');
        if (count($templateColumn)) {
            $observer->getBlock()->setTemplate($templateColumn[0]);
        }
    }
}
