<?php
namespace Swissup\Pagespeed\Model\Patch;

interface PatcherInterface
{
    /**
     * Perform result postprocessing
     *
     * @param \Magento\Framework\App\Response\Http $response
     * @return void
     */
    public function apply(?\Magento\Framework\App\Response\Http $response = null);

    public function restore();
}
