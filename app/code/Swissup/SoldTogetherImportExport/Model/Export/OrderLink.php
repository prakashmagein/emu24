<?php

namespace Swissup\SoldTogetherImportExport\Model\Export;

use Magento\Eav\Model\Entity\AttributeFactory;
use Swissup\SoldTogether\Model\ResourceModel\Order as Resource;
use Swissup\SoldTogetherImportExport\Model\Export\OrderLink\CollectionFactory as EntityCollectionFactory;

class OrderLink extends AbstractLink
{
    /**
     * @var AttributeFactory
     */
    private $attributeFactory;

    /**
     * @param AttributeFactory        $attributeFactory
     * @param EntityCollectionFactory $entityCollectionFactory
     * @param Resource                $resource
     * @param Context                 $context
     * @param array                   $data
     */
    public function __construct(
        AttributeFactory $attributeFactory,
        EntityCollectionFactory $entityCollectionFactory,
        Resource $resource,
        Context $context,
        array $data = []
    ) {
        $this->attributeFactory = $attributeFactory;

        parent::__construct($entityCollectionFactory, $resource, $context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityTypeCode()
    {
        return 'soldtogether_order';
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeCollection()
    {
        $attributeCollection = parent::getAttributeCollection();

        $promoRuleAttribute = $attributeCollection->getItemById('promo_rule');
        if (!$promoRuleAttribute) {
            $promoRuleAttribute = $this->attributeFactory->create();
            $promoRuleAttribute->setId('promo_rule');
            $promoRuleAttribute->setDefaultFrontendLabel('Promo Rule');
            $promoRuleAttribute->setAttributeCode('promo_rule');
            $promoRuleAttribute->setBackendType('varchar');
            $promoRuleAttribute->setFrontendInput('select');
            $promoRuleAttribute->setSourceModel(Source\PromoRule::class);
            $attributeCollection->addItem($promoRuleAttribute);

            $promoValueAttribute = $this->attributeFactory->create();
            $promoValueAttribute->setId('promo_value');
            $promoValueAttribute->setBackendType('decimal');
            $promoValueAttribute->setDefaultFrontendLabel('Promo Value');
            $promoValueAttribute->setAttributeCode('promo_value');
            $attributeCollection->addItem($promoValueAttribute);
        }

        return $attributeCollection;
    }
}
