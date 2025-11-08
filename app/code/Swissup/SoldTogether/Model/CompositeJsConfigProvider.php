<?php

namespace Swissup\SoldTogether\Model;

use Magento\Framework\View\Element\Template;

class CompositeJsConfigProvider
{
    private array $configProviders;

    public function __construct(
        array $configProviders = []
    ) {
        $this->configProviders = $configProviders;
    }

    public function getConfig(Template $ctxBlock): array
    {
        $config = [];
        foreach ($this->configProviders as $configProvider) {
            $config = array_merge_recursive(
                $config,
                $configProvider->getConfig($ctxBlock)
            );
        }
        return $config;
    }
}
