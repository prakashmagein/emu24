<?php

namespace Swissup\Pagespeed\Installer\Command;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Swissup\Pagespeed\Installer\Command\Traits\LoggerAware;

class CheckImageOptimizers
{
    use LoggerAware;

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
     * @param \Swissup\Marketplace\Installer\Request $request
     * @return void
     */
    public function execute($request)
    {
        if (!$this->checker->isAllExecutable()) {
            $this->getLogger()->warning($this->checker->getMainMessage());
        }
    }
}
