<?php

namespace Swissup\SoldTogetherImportExport\Model\Export;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\ImportExport\Model\Export\Factory as CollectionFactory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Magento\Store\Model\StoreManagerInterface;

class Context
{ 
    /**
     * @var Link\AttributeCollectionProvider
     */
    private $attributeCollectionProvider;

    /**
     * @var HeaderProvider
     */
    private $headerProvider;

    /**
     * @var CollectionByPagesIteratorFactory
     */
    private $resourceColFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Link\AttributeCollectionProvider $attributeCollectionProvider
     * @param CollectionByPagesIteratorFactory $resourceColFactory
     * @param CollectionFactory                $collectionFactory
     * @param HeaderProvider                   $headerProvider
     * @param ScopeConfigInterface             $scopeConfig
     * @param StoreManagerInterface            $storeManager
     */
    public function __construct(
        Link\AttributeCollectionProvider $attributeCollectionProvider,
        CollectionByPagesIteratorFactory $resourceColFactory,
        CollectionFactory $collectionFactory,
        HeaderProvider $headerProvider,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->attributeCollectionProvider = $attributeCollectionProvider;
        $this->resourceColFactory = $resourceColFactory;
        $this->collectionFactory = $collectionFactory;
        $this->headerProvider = $headerProvider;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @return Link\AttributeCollectionProvider
     */
    public function getAttributeCollectionProvider()
    {
        return $this->attributeCollectionProvider;
    }

    /**
     * @return CollectionByPagesIteratorFactory
     */
    public function getResourceColFactory()
    {
        return $this->resourceColFactory;
    }

    /**
     * @return CollectionFactory
     */
    public function getCollectionFactory()
    {
        return $this->collectionFactory;
    }
    
    /**
     * @return HeaderProvider
     */
    public function getHeaderProvider()
    {
        return $this->headerProvider;
    }

    /**
     * @return ScopeConfigInterface
     */
    public function getScopeConfig()
    {
        return $this->scopeConfig;
    }

    /**
     * @return StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }
}
