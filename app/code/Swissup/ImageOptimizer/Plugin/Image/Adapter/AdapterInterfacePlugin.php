<?php

namespace Swissup\ImageOptimizer\Plugin\Image\Adapter;

class AdapterInterfacePlugin
{
    use OptimizerTrait;

    /**
     * @param \Magento\Framework\Image\Adapter\AdapterInterface $subject
     * @param null $result
     * @param null|string $destination
     * @param null|string $newName
     * @return void
     */
    public function afterSave(\Magento\Framework\Image\Adapter\AdapterInterface $subject, $result = null, $destination = null, $newName = null)
    {
        // accessing protected method \Magento\Framework\Image\Adapter\AdapterInterface::_prepareDestination (with Closure)
        $filename = (fn() => $this->_prepareDestination($destination, $newName))->call($subject);// @phpstan-ignore-line
        // accessing protected property \Magento\Framework\Image\Adapter\AdapterInterface::logger (with Closure)
        $this->setLogger((fn() => $this->logger)->call($subject));// @phpstan-ignore-line

        if (!empty($filename)) {
            $this->optimize($filename);
        }
    }
}
