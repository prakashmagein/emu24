<?php
namespace Swissup\Easybanner\Model\Banner\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Placeholder implements OptionSourceInterface
{
    /**
     * @var \Swissup\Easybanner\Model\ResourceModel\Placeholder\CollectionFactory
     */
    protected $placeholderCollectionFactory;

    /**
     * @param \Swissup\Easybanner\Model\ResourceModel\Placeholder\CollectionFactory $placeholderCollectionFactory
     */
    public function __construct(
        \Swissup\Easybanner\Model\ResourceModel\Placeholder\CollectionFactory $placeholderCollectionFactory
    ) {
        $this->placeholderCollectionFactory = $placeholderCollectionFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $collection = $this->placeholderCollectionFactory->create();
        foreach ($collection as $model) {
            $options[] = [
                'value' => $model->getId(),
                'label' => $model->getName(),
            ];
        }
        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $result = [];
        foreach ($this->toOptionArray() as $item) {
            $result[$item['value']] = $item['label'];
        }
        return $result;
    }
}
