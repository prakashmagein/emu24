<?php
namespace Swissup\Amp\Observer;

use Magento\Framework\Event\ObserverInterface;

class LayoutLoadBefore implements ObserverInterface
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
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helper->canUseAmp()) return;

        // add additional layout handles with 'swissupamp_' prefix
        $update = $observer->getLayout()->getUpdate();
        $update->addHandle('swissupamp');
        foreach ($update->getHandles() as $handle) {
            if ('swissupamp' === $handle) {
                continue;
            }

            $update->addHandle(
                'swissupamp_' . $handle
            );
        }
    }
}
