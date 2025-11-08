<?php
namespace Swissup\Amp\Observer;

use Magento\Framework\Event\ObserverInterface;

class ViewBlockAbstractToHtmlAfter implements ObserverInterface
{
    /**
     * @var \Swissup\Amp\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @param \Swissup\Amp\Helper\Data $helper
     * @param \Magento\Framework\View\LayoutInterface $layout
     */
    public function __construct(
        \Swissup\Amp\Helper\Data $helper,
        \Magento\Framework\View\LayoutInterface $layout
    ) {
        $this->helper = $helper;
        $this->layout = $layout;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helper->canUseAmp()) return;

        $this->supressBlockOutput($observer);
        $this->prepareLightboxes($observer);
        $this->addScssPartials($observer);
        $this->addAmpComponents($observer);
    }

    /**
     * Supress non-whitelisted blocks
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    protected function supressBlockOutput(\Magento\Framework\Event\Observer $observer)
    {
        $node = $this->helper->getConfig('swissup_amp/whitelist/block_types');
        $whitelist = [];
        foreach ($node as $nodeName => $blockTypes) {
            $whitelist = array_merge($whitelist, array_column($blockTypes, 'class'));
        }

        $blockClass = $this->helper->getCleanClass($observer->getBlock());
        if (!in_array($blockClass, $whitelist)) {
            $observer->getTransport()->setHtml('');
        }
    }

    /**
     * Wrap some blocks into amp-lightbox component
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    protected function prepareLightboxes(\Magento\Framework\Event\Observer $observer)
    {
        $block = $observer->getBlock();
        $nodes = $this->helper->getConfig('swissup_amp/lightbox/blocks');
        foreach ($nodes as $node) {
            if (!($block instanceof $node['class'])) {
                continue;
            }

            $lightboxId = $node['id'];
            $html = $observer->getTransport()->getHtml();
            $observer->getTransport()->setHtml(
                '<amp-lightbox id="' . $lightboxId . '" layout="nodisplay" scrollable>'
                    . '<div class="lightbox">'
                        . $html
                        . '<button class="close close-icon" on="tap:' . $lightboxId . '.close"></button>'
                    . '</div>'
                . '</div>'
            );
        }
    }

    /**
     * Add scss partials, if needed
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    protected function addScssPartials(\Magento\Framework\Event\Observer $observer)
    {
        $html = (string) $observer->getTransport()->getHtml();
        if (empty(trim($html))) {
            return;
        }

        if (!($scssHead = $this->layout->getBlock('swissupamp.styles'))) {
            return;
        }

        $block = $observer->getBlock();
        $rules = $this->helper->getConfig('swissup_amp/includes/blocks');
        foreach ($rules as $rule) {
            if (!isset($rule['styles']) || !($block instanceof $rule['class'])) {
                continue;
            }

            foreach ($rule['styles'] as $key => $file) {
                $scssHead->addItem($file);
            }
        }
    }

    /**
     * Add amp-element scripts, if needed
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    protected function addAmpComponents(\Magento\Framework\Event\Observer $observer)
    {
        $html = (string) $observer->getTransport()->getHtml();
        if (empty(trim($html))) {
            return;
        }

        if (!($jsHead = $this->layout->getBlock('swissupamp.scripts'))) {
            return;
        }

        $block = $observer->getBlock();
        $rules = $this->helper->getConfig('swissup_amp/includes/blocks');
        foreach ($rules as $rule) {
            if (!isset($rule['scripts']) || !($block instanceof $rule['class'])) {
                continue;
            }

            foreach ($rule['scripts'] as $key => $component) {
                $jsHead->addItem(
                    $component['custom-element'],
                    $component['src']
                );
            }
        }
    }
}
