<?php
namespace Swissup\Attributepages\Model\Config\Source;

class Attributes implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Swissup\Attributepages\Model\ResourceModel\Catalog\Product\Attribute\CollectionFactory
     */
    protected $attrCollectionFactory;

    /**
     * @var \Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory
     */
    protected $attrPageCollectionFactory;

    /**
     * Constructor
     *
     * @param \Swissup\Attributepages\Model\ResourceModel\Catalog\Product\Attribute\CollectionFactory $attrCollectionFactory
     * @param \Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory $attrPageCollectionFactory
     */
    public function __construct(
        \Swissup\Attributepages\Model\ResourceModel\Catalog\Product\Attribute\CollectionFactory $attrCollectionFactory,
        \Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory $attrPageCollectionFactory
    ) {
        $this->attrCollectionFactory = $attrCollectionFactory;
        $this->attrPageCollectionFactory = $attrPageCollectionFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $pages = $this->getPagesCollection();
        $attributes = [];
        if ($pages->getSize()) {
            $attributes = $this->attrCollectionFactory->create()
                ->setFrontendInputTypeFilter(['select', 'multiselect'])
                ->addOrder('frontend_label', 'ASC')
                ->addFieldToFilter('main_table.attribute_id', [
                    'in' => array_unique($pages->getColumnValues('attribute_id'))
                ])
                ->toOptionArray();
        }
        return $attributes;
    }

    /**
     * @return array
     */
    public function toOptionHash()
    {
        $result = [];

        foreach ($this->toOptionArray() as $option) {
            $result[$option['value']] = $option['label'];
        }

        return $result;
    }

    private function getPagesCollection()
    {
        $collection = $this->attrPageCollectionFactory->create()
            ->addAttributeOnlyFilter();

        return $collection;
    }
}
