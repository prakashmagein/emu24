<?php

namespace Swissup\Highlight\Model\ResourceModel\Product;

class CollectionFactory
{
    const TYPE_DEFAULT = 'default';
    const TYPE_BESTSELLERS = 'bestsellers';
    const TYPE_BULKSELLERS = 'bulksellers';
    const TYPE_POPULAR = 'popular';
    const TYPE_ONSALE = 'onsale';

    /**
     * @var array
     */
    protected $typeClasses = [
        self::TYPE_DEFAULT     => \Swissup\Highlight\Model\ResourceModel\Product\Collection::class,
        self::TYPE_BESTSELLERS => \Swissup\Highlight\Model\ResourceModel\Product\Bestsellers\Collection::class,
        self::TYPE_BULKSELLERS => \Swissup\Highlight\Model\ResourceModel\Product\Bulksellers\Collection::class,
        self::TYPE_POPULAR     => \Swissup\Highlight\Model\ResourceModel\Product\Popular\Collection::class,
        self::TYPE_ONSALE      => \Swissup\Highlight\Model\ResourceModel\Product\Collection::class,
    ];

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create menu item from array
     *
     * @param string $type
     * @param array $data
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function create($type, array $data = [])
    {
        if (!isset($this->typeClasses[$type])) {
            throw new \InvalidArgumentException("{$type} is not a valid type");
        }
        return $this->objectManager->create($this->typeClasses[$type], $data);
    }
}
