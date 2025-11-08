<?php

namespace Swissup\SeoCrossLinks\Plugin\Megefan\Block;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Swissup\SeoCrossLinks\Helper\Data;
use Swissup\SeoCrossLinks\Model\Filter;
use Swissup\SeoCrossLinks\Model\Link;

class Post
{
    /**
     * @var \Swissup\SeoCrossLinks\Helper\Data
     */
    private $helper;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param \Swissup\SeoCrossLinks\Helper\Data $helper
     */
    public function __construct(
        Data $helper,
        Filter $filter,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->helper = $helper;
        $this->filter = $filter;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param Object $result
     */
    public function afterGetPost(\Magefan\Blog\Block\Post\AbstractPost $subject, $result)
    {
        // if (!$this->helper->IsEnabled() || empty($result)) {
        //     return $result;
        // }

        //if Magefan Post integration is enabled
        $storeId = $this->storeManager->getStore()->getId();
        $isMagefanIntegrationEnabled = $this->scopeConfig->isSetFlag(
            'seo_cross_links/general/enabled_for_magefun_post',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (!$isMagefanIntegrationEnabled || empty($result)) {
            return $result;
        }

        $content = $result->getContent();

        if (is_string($content)) {
            $content = $this->filter
                ->setMode(Link::SEARCH_IN_CMS)
                ->setStoreId($this->storeManager->getStore()->getId())
                ->filter($content, true);
            $result->setContent($content);
        }

        return $result;
    }
}