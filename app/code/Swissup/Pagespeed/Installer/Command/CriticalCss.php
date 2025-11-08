<?php

namespace Swissup\Pagespeed\Installer\Command;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

use Swissup\Pagespeed\Installer\Command\Traits\LoggerAware;

class CriticalCss
{
    use LoggerAware;

    /**
     * @var \Swissup\Pagespeed\Model\Css\GetCriticalCss
     */
    private $service;

    /**
     *
     * @param \Swissup\Pagespeed\Model\Css\GetCriticalCss $service
     */
    public function __construct(\Swissup\Pagespeed\Model\Css\GetCriticalCss $service)
    {
        $this->service = $service;
    }

    /**
     * @param \Swissup\Marketplace\Installer\Request $request
     * @return void
     */
    public function execute($request)
    {
        foreach ($request->getStoreIds() as $storeId) {
            try {
                $this->service
                    ->setStore($storeId)
                    ->generateDefault()
                    ->saveConfig();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->getLogger()->error($e->getMessage());
            }
        }
    }
}
