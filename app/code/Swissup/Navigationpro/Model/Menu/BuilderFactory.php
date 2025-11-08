<?php

namespace Swissup\Navigationpro\Model\Menu;

class BuilderFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Swissup\Navigationpro\Model\Config\Source\BuilderType
     */
    protected $builderType;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Swissup\Navigationpro\Model\Config\Source\BuilderType $builderType
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Swissup\Navigationpro\Model\Config\Source\BuilderType $builderType
    ) {
        $this->objectManager = $objectManager;
        $this->builderType = $builderType;
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
        $allowedTypes = $this->builderType->getTypeClasses();
        if (!isset($allowedTypes[$type])) {
            throw new \InvalidArgumentException("{$type} is not a valid type");
        }
        return $this->objectManager->create($allowedTypes[$type], $data);
    }
}
