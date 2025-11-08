<?php

declare(strict_types=1);

namespace Swissup\EasySlide\Model\Config\ContentType\AdditionalData\Provider;

use Magento\PageBuilder\Model\Config\ContentType\AdditionalData\ProviderInterface;

if (!interface_exists(ProviderInterface::class)) {
    class_alias(
        \Swissup\EasySlide\Model\Config\ContentType\AdditionalData\Provider\ProviderInterface::class,
        ProviderInterface::class
    );
}
/**
 * Provides URL for retrieving block metadata
 */
class IdentifierDataUrl implements ProviderInterface
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * IdentifierDataUrl constructor.
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(\Magento\Framework\UrlInterface $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @inheritdoc
     */
    public function getData(string $itemName): array
    {
        return [$itemName => $this->urlBuilder->getUrl('easyslide/contenttype_slider/metadata')];
    }
}
