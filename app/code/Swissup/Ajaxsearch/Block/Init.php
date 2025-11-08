<?php
/**
 * Copyright © 2016-2020 Swissup. All rights reserved.
 */
namespace Swissup\Ajaxsearch\Block;

use Magento\Checkout\Model\Session as CheckoutSession;

class Init extends \Swissup\Ajaxsearch\Block\Template
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json $serializer
     */
    private $serializer;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    private $localeFormat;

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    private $catalogLayer;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Swissup\Ajaxsearch\Helper\Data $configHelper
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Swissup\Ajaxsearch\Helper\Data $configHelper,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        array $data = []
    ) {
        parent::__construct($context, $configHelper, $data);

        $this->serializer = $serializer;
        $this->localeFormat = $localeFormat;
        $this->catalogLayer = $layerResolver->get();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        if ($this->configHelper->isEnabled()) {
            $this->pageConfig->addBodyClass('swissup-ajaxsearch-loading');

            if ($this->configHelper->isFoldedDesignEnabled()) {
                $this->pageConfig->addBodyClass('swissup-ajaxsearch-folded-loading');
            }
        }

        return parent::_prepareLayout();
    }

    /**
     * @return bool|string
     */
    public function getSettings()
    {
        $currency = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        /** @var \Magento\Framework\Locale\Format $format */
        $format = $this->localeFormat;
        $priceFormat = $format->getPriceFormat(null, $currency);
        $config = [
            'priceFormat' => $priceFormat
        ];
        return $this->serializer->serialize($config);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreViewCode()
    {
        return $this->_storeManager->getStore()->getCode();
    }

    /**
     * @return \Magento\Catalog\Model\Category
     */
    public function getCurrentCategory()
    {
        return $this->catalogLayer->getCurrentCategory();
    }
}
