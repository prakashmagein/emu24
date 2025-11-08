<?php

declare(strict_types=1);

namespace Swissup\Easybanner\Model\Config\ContentType\AdditionalData\Provider;

use Magento\PageBuilder\Model\Config\ContentType\AdditionalData\ProviderInterface;

if (!interface_exists(ProviderInterface::class)) {
    class_alias(
        \Swissup\Easybanner\Model\Config\ContentType\AdditionalData\ProviderInterface::class,
        ProviderInterface::class
    );
}

/**
 * Provides URL for retrieving block metadata
 */
class BannerDataUrl implements ProviderInterface
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * BlockDataUrl constructor.
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(\Magento\Framework\UrlInterface $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @inheritdoc
     */
    public function getData(string $itemName) : array
    {
        return [$itemName => $this->urlBuilder->getUrl('easybanner/contenttype_banner/metadata')];
    }
}
