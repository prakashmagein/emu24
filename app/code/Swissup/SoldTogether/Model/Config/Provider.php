<?php

namespace Swissup\SoldTogether\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Provider
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var string
     */
    protected $linkType;

    /**
     * @param  ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        string $linkType = ''
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->linkType = $linkType;
    }

    public function getLinkType(): string
    {
        return $this->linkType;
    }

    private function readConfig($key) {
        return $this->scopeConfig->getValue(
            sprintf("soldtogether/%s/%s", $this->linkType, $key),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getAllowedProductTypes(): array
    {
        return explode(',', $this->readConfig('allowed_product_types'));
    }

    public function isShowOutOfStock(): bool
    {
        return !!$this->readConfig('out');
    }

    public function isRandom(): bool
    {
        return !!$this->readConfig('random');
    }

    public function getCount(): int
    {
        return (int)$this->readConfig('count');
    }

    public function getLayout(): string
    {
        return $this->readConfig('layout') ?? '';
    }

    public function isEnabled(): bool
    {
        return !!$this->readConfig('enabled');
    }

    public function getTaxDisplay(): string
    {
        return $this->scopeConfig->getValue(
            'tax/display/type',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
