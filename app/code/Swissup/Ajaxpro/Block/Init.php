<?php
/**
 * Copyright © 2016-2020 Swissup. All rights reserved.
 */
namespace Swissup\Ajaxpro\Block;

class Init extends Template
{
    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json $serializer
     */
    private $serializer;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Swissup\Ajaxpro\Helper\Config $configHelper
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Swissup\Ajaxpro\Helper\Config $configHelper,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        array $data = []
    ) {
        parent::__construct($context, $configHelper, $data);

        $this->serializer = $serializer;
        $this->urlHelper = $urlHelper;
    }

    /**
     *
     * @return string
     */
    public function getCustomerSectionLoadUrl()
    {
        return $this->getUrl(
            'customer/section/load',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }

    /**
     * @return array
     */
    private function getWidgetBaseConfig()
    {
        $sectionLoadUrl = $this->getCustomerSectionLoadUrl();
        $uenc = $this->urlHelper->getEncodedUrl();

        $config = [
            'sectionLoadUrl' => $sectionLoadUrl,
            'refererParam' => \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED,
            'refererQueryParamName' => \Magento\Customer\Model\Url::REFERER_QUERY_PARAM_NAME,
            'refererValue' => $uenc
        ];

        return $config;
    }

    /**
     *
     * @return string
     */
    public function getJsonEncodedCatalogProductViewConfig()
    {
        $config = $this->getWidgetBaseConfig();
        return $this->serializer->serialize($config);
    }

    /**
     * @return array
     */
    private function getLoaderConfig()
    {
        return [
            'loaderImage' => $this->getViewFileUrl('images/loader-1.gif'),
            'loaderImageMaxWidth' => '20px'
        ];
    }

    /**
     * @return bool|string
     */
    public function getJsonEncodedAjaxcianDataPostConfig()
    {
        $config = $this->getLoaderConfig();
        return $this->serializer->serialize($config);
    }

    /**
     * @return bool|string
     */
    public function getJsonEncodedQuickViewConfig()
    {
        $config = $this->getLoaderConfig();
        return $this->serializer->serialize($config);
    }

    public function getJsonEncodedMinicartOverrideViewConfig()
    {
        $config = ['override_minicart' => $this->configHelper->isOverrideMinicart()];
        return $this->serializer->serialize($config);
    }
}
