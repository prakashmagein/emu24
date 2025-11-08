<?php

namespace Swissup\Ajaxsearch\Search;

use Magento\Framework\ObjectManagerInterface;

class EngineResolverInterfaceFactory
{
    /**
     * Entity class name
     */
    const CLASS_NAME = \Magento\Framework\Search\EngineResolverInterface::class;

    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     *
     * @return bool
     */
    public function isExist()
    {
        return interface_exists(self::CLASS_NAME) || interface_exists(self::CLASS_NAME, false);
    }

    /**
     *
     * @return \Magento\Framework\Search\EngineResolverInterface
     */
    public function create()
    {
        return $this->objectManager->create(self::CLASS_NAME);
    }
}
