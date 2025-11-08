<?php

namespace Swissup\SoldTogetherImportExport\Model\Export\Link;

use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Framework\Data\Collection;
use Magento\ImportExport\Model\Export\Factory as CollectionFactory;
use Swissup\SoldTogetherImportExport\Model\Export\Source\IsAdminStatus;

class AttributeCollectionProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @param CollectionFactory $collectionFactory
     * @param AttributeFactory $attributeFactory
     * @throws \InvalidArgumentException
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        AttributeFactory $attributeFactory
    ) {
        $this->collection = $collectionFactory->create(Collection::class);
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * @return Collection
     * @throws \Exception
     */
    public function get(): Collection
    {
        if (count($this->collection) === 0) {
            /** @var \Magento\Eav\Model\Entity\Attribute $productIdAttribute */
            $productIdAttribute = $this->attributeFactory->create();
            $productIdAttribute->setId('product_id');
            $productIdAttribute->setBackendType('int');
            $productIdAttribute->setDefaultFrontendLabel('Product ID');
            $productIdAttribute->setAttributeCode('product_id');
            $this->collection->addItem($productIdAttribute);

            /** @var \Magento\Eav\Model\Entity\Attribute $productSkuAttribute */
            $productSkuAttribute = $this->attributeFactory->create();
            $productSkuAttribute->setId('product_sku');
            $productSkuAttribute->setBackendType('varchar');
            $productSkuAttribute->setDefaultFrontendLabel('Product SKU');
            $productSkuAttribute->setAttributeCode('product_sku');
            $this->collection->addItem($productSkuAttribute);

            /** @var \Magento\Eav\Model\Entity\Attribute $relatedIdAttribute */
            $relatedIdAttribute = $this->attributeFactory->create();
            $relatedIdAttribute->setId('related_id');
            $relatedIdAttribute->setBackendType('int');
            $relatedIdAttribute->setDefaultFrontendLabel('Related ID');
            $relatedIdAttribute->setAttributeCode('related_id');
            $this->collection->addItem($relatedIdAttribute);

            /** @var \Magento\Eav\Model\Entity\Attribute $relatedSkuAttribute */
            $relatedSkuAttribute = $this->attributeFactory->create();
            $relatedSkuAttribute->setId('related_sku');
            $relatedSkuAttribute->setBackendType('varchar');
            $relatedSkuAttribute->setDefaultFrontendLabel('Related SKU');
            $relatedSkuAttribute->setAttributeCode('related_sku');
            $this->collection->addItem($relatedSkuAttribute);

            /** @var \Magento\Eav\Model\Entity\Attribute $weightAttribute */
            $weightAttribute = $this->attributeFactory->create();
            $weightAttribute->setId('weight');
            $weightAttribute->setBackendType('int');
            $weightAttribute->setDefaultFrontendLabel('Weight');
            $weightAttribute->setAttributeCode('weight');
            $this->collection->addItem($weightAttribute);

            /** @var \Magento\Eav\Model\Entity\Attribute $isAdminAttribute */
            $isAdminAttribute = $this->attributeFactory->create();
            $isAdminAttribute->setId('is_admin');
            $isAdminAttribute->setDefaultFrontendLabel('Is Admin');
            $isAdminAttribute->setAttributeCode('is_admin');
            $isAdminAttribute->setBackendType('int');
            $isAdminAttribute->setFrontendInput('select');
            $isAdminAttribute->setSourceModel(IsAdminStatus::class);
            $this->collection->addItem($isAdminAttribute);
        }

        return $this->collection;
    }
}
