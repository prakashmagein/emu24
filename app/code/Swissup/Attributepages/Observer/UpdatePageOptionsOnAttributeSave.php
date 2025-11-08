<?php
namespace Swissup\Attributepages\Observer;

use Magento\Framework\Event\ObserverInterface;

class UpdatePageOptionsOnAttributeSave implements ObserverInterface
{
    /**
     * @var \Swissup\Attributepages\Model\EntityFactory
     */
    protected $attributepagesModel;
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory
     */
    protected $attrOptionCollectionFactory;
    /**
     * Resource instance
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $coreResource;
    /**
     * @param \Swissup\Attributepages\Model\EntityFactory $attributepagesModel
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Framework\App\ResourceConnection $coreResource
     */
    public function __construct(
        \Swissup\Attributepages\Model\EntityFactory $attributepagesModel,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Framework\App\ResourceConnection $coreResource
    )
    {
        $this->attributepagesModel = $attributepagesModel;
        $this->attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->coreResource = $coreResource;
    }
    /**
     * Check and update page options on attribute save
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $attribute = $observer->getAttribute();

        $attributepagesOptions = $this->attributepagesModel->create()
            ->getCollection()
            ->addOptionOnlyFilter()
            ->addFieldToFilter('attribute_id', $attribute->getAttributeId());
        $existingIds = $attributepagesOptions->getColumnValues('option_id');

        $eavOptions = $this->attrOptionCollectionFactory->create()
            ->setAttributeFilter($attribute->getAttributeId())
            ->addFieldToFilter('main_table.option_id', ['nin' => $existingIds]);
        $table = $this->coreResource->getTableName('eav_attribute_option_value');
        $eavOptions->getSelect()
            ->joinLeft(
                ['sort_alpha_value' => $table],
                'sort_alpha_value.option_id = main_table.option_id AND sort_alpha_value.store_id = 0',
                ['value']
            );

        foreach ($eavOptions as $option) {
            $entity = $this->attributepagesModel->create();
            $entity->importOptionData($option);
            try {
                $entity->save();
            } catch (\Exception $e) {
                //
            }
        }
    }
}
