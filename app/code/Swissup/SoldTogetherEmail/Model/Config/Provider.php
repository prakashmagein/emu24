<?php

namespace Swissup\SoldTogetherEmail\Model\Config;

class Provider extends \Swissup\SoldTogether\Model\Config\Provider
{
    /**
     * {@inheritdoc}
     */
    public function getAllowedProductTypes(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function isShowOutOfStock(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isRandom(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount(): int
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return true;
    }
}
