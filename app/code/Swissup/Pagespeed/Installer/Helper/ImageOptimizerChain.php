<?php

namespace Swissup\Pagespeed\Installer\Helper;

class ImageOptimizerChain
{
    /**
     * @var \Swissup\ImageOptimizer\Model\CheckImageOptimizerExisting
     */
    private $checker;

    /**
     *
     * @param \Swissup\ImageOptimizer\Model\CheckImageOptimizerExisting $checker
     */
    public function __construct(\Swissup\ImageOptimizer\Model\CheckImageOptimizerExisting $checker)
    {
        $this->checker = $checker;
    }

    /**
     * @param array $request
     * @return boolean
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(array $request)
    {
        return $this->checker->isAllExecutable();
    }
}
