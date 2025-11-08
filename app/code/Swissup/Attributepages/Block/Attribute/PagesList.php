<?php

namespace Swissup\Attributepages\Block\Attribute;

use Magento\Framework\View\Element\Template;
use Magento\Framework\DataObject\IdentityInterface;
use Swissup\Attributepages\Model\Entity as AttributepagesModel;

class PagesList extends Template implements IdentityInterface
{
    private \Magento\Framework\App\Http\Context $httpContext;

    /**
     * @var \Swissup\Attributepages\Model\ResourceModel\Entity\Collection
     */
    protected $attributeCollection;

    protected \Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory $attributeCollectionFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory $attributeCollectionFactory,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->httpContext = $httpContext;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addData(
            [
                'cache_lifetime' => 86400
            ]
        );
    }

    /**
     * @return Swissup\Attributepages\Model\ResourceModel\Entity\Collection
     */
    protected function _getAttributeCollection()
    {
        if (null === $this->attributeCollection) {
            $storeId = $this->_storeManager->getStore()->getId();
            $this->attributeCollection = $this->attributeCollectionFactory->create()
                ->addAttributeOnlyFilter()
                ->addUseForAttributePageFilter()
                ->addStoreFilter($storeId)
                ->setOrder('main_table.sort_order', 'asc')
                ->setOrder('main_table.title', 'asc');

            if ($excludedPages = $this->getExcludedPages()) {
                $excludedPages = explode(',', $excludedPages);
                $this->attributeCollection
                    ->addFieldToFilter('identifier', ['nin' => $excludedPages]);
            }

            if ($includedPages = $this->getIncludedPages()) {
                $includedPages = explode(',', $includedPages);
                $this->attributeCollection
                    ->addFieldToFilter('identifier', ['in' => $includedPages]);
            }

            // filter pages with the same urls: linked to All Store Views and current store
            $urls = $this->attributeCollection->getColumnValues('identifier');
            $duplicateUrls = [];
            foreach (array_count_values($urls) as $url => $count) {
                if ($count > 1) {
                    $duplicateUrls[] = $url;
                }
            }

            foreach ($duplicateUrls as $url) {
                $idsToRemove = [];
                $removeFlag = false;
                $attributes = $this->attributeCollection->getItemsByColumnValue('identifier', $url);

                foreach ($attributes as $attribute) {
                    if (!in_array($storeId, $attribute->getStores())) {
                        $idsToRemove[] = $attribute->getId();
                    } else {
                        $removeFlag = true;
                    }
                }

                if ($removeFlag) {
                    foreach ($idsToRemove as $id) {
                        $this->attributeCollection->removeItemByKey($id);
                    }
                }
            }
        }

        return $this->attributeCollection;
    }

    /**
     * @return Swissup\Attributepages\Model\ResourceModel\Entity\Collection
     */
    public function getLoadedAttributeCollection()
    {
        return $this->_getAttributeCollection();
    }

    public function setCollection($collection)
    {
        $this->attributeCollection = $collection;
        return $this;
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        $result = [];

        foreach ($this->getLoadedAttributeCollection() as $entity) {
            $result[] = AttributepagesModel::CACHE_TAG . '_' . $entity->getId();
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return [
            'ATTRIBUTEPAGES',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
            'template' => $this->getTemplate(),
            $this->getColumnCount(),
            $this->getExcludedPages(),
            $this->getIncludedPages()
        ];
    }
}
