<?php

namespace Swissup\Fblike\Block;

class Button extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    private $configSection = 'category';

    /**
     * Get config section name
     *
     * @return string
     */
    public function getConfigSection()
    {
        return $this->configSection;
    }

    /**
     * Set confgi section name
     *
     * @param string
     */
    public function setConfigSection($value)
    {
        $this->configSection = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getLikeButtonConfig()
    {
        return $this->_scopeConfig->getValue(
            "fblike/" . $this->getConfigSection(),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getProductUrl()
    {
        $product = $this->getProduct();
        if (isset($product)) {
            $oldRequestPath = $product->getData('request_path');
            $product->setData('request_path', '');
            $params = ['_ignore_category' => true];
            $url = $product->getUrlModel()->getUrl($product, $params);
            $product->setData('request_path', $oldRequestPath);
            return $url;
        }

        return '';
    }

    /**
     * Retrieve script options encoded to json
     *
     * @return string
     */
    public function getFacebookOptions()
    {
        $appId = $this->_scopeConfig->getValue(
            'fblike/general/app',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return json_encode(['appId' => $appId]);
    }
}
