<?php

namespace Swissup\Ajaxsearch\ViewModel;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Config extends DataObject implements ArgumentInterface
{
    /**
     *
     * @var \Swissup\Ajaxsearch\Helper\Data
     */
    private $configHelper;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json $serializer
     */
    private $serializer;


    /**
     * @param \Swissup\Ajaxsearch\Helper\Data $configHelper
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Swissup\Ajaxsearch\Helper\Data $configHelper,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        array $data = []
    ) {
        $this->configHelper = $configHelper;
        $this->serializer = $serializer;
        parent::__construct();
    }

    /**
     * @return bool
     */
    public function isCategoryFilterLoadOptionsByGraphql()
    {
        return $this->configHelper->isCategoryFilterLoadOptionsByGraphql();
    }

    /**
     * @return bool
     */
    public function isCategoryFilterEnabled()
    {
        return $this->configHelper->isCategoryFilterEnabled();
    }

    /**
     * @return string
     */
    public function getCategoryVarName()
    {
        return $this->configHelper->getCategoryVarName();
    }

    /**
     * @return string
     */
    public function getWildcard()
    {
        return $this->configHelper->getWildcard();
    }

    /**
     * @param $query
     * @return string
     */
    public function getAjaxActionUrl($query = null)
    {
        return $this->configHelper->getAjaxActionUrl($query);
    }

    /**
     * @return int|string
     */
    public function getMinQueryLength()
    {
        return $this->configHelper->getMinQueryLength();
    }

    /**
     * @return string
     */
    public function getGraphQlUrl()
    {
        return $this->configHelper->getGraphQlUrl();
    }

    /**
     * @return bool
     */
    public function isProductViewAllEnabled()
    {
        return $this->configHelper->isProductViewAllEnabled();
    }

    /**
     * @return bool
     */
    public function useQraphQl()
    {
        return $this->configHelper->useQraphQl();
    }

    /**
     * @return string
     */
    public function getAdditionalCssClass()
    {
        return $this->configHelper->getAdditionalCssClass();
    }

    /**
     * @return bool
     */
    public function isFoldedDesignEnabled()
    {
        return $this->configHelper->isFoldedDesignEnabled();
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->configHelper->getLimit();
    }

    /**
     *
     * @return boolean
     */
    public function isHighligth()
    {
        return $this->configHelper->isHighligth();
    }

    /**
     *
     * @return boolean
     */
    public function isHint()
    {
        return $this->configHelper->isHint();
    }

    /**
     *
     * @return string [json]
     */
    public function getClassNames()
    {
        $classNames = $this->configHelper->getClassNames();

        return $this->serializer->serialize($classNames);
    }
}
