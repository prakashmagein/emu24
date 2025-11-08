<?php

namespace Swissup\Navigationpro\Model\Config\Source;

class Menu implements \Magento\Framework\Option\ArrayInterface
{
    protected $menuCollectionFactory;

    /**
     * @param \Swissup\Navigationpro\Model\ResourceModel\Menu\CollectionFactory $menuCollectionFactory
     */
    public function __construct(
        \Swissup\Navigationpro\Model\ResourceModel\Menu\CollectionFactory $menuCollectionFactory
    ) {
        $this->menuCollectionFactory = $menuCollectionFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $collection = $this->menuCollectionFactory->create();
        $options = [
            ['value' => 0, 'label' => __('No')]
        ];
        foreach ($collection as $menu) {
            $options[] = [
                'value' => $menu->getIdentifier(),
                'label' => $menu->getIdentifier(),
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
