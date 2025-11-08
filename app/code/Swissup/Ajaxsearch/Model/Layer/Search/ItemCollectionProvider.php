<?php

namespace Swissup\Ajaxsearch\Model\Layer\Search;

use Magento\Catalog\Model\Layer\ItemCollectionProviderInterface;

/**
 * Catalog search category layer collection provider.
 * @todo add for back compatability with 2.3.1
 */
class ItemCollectionProvider implements ItemCollectionProviderInterface
{
    /**
     * @var \Swissup\Ajaxsearch\Search\EngineResolverInterfaceFactory
     */
    private $engineResolverInterfaceFactory;

    /**
     * @var array
     */
    private $factories;

    /**
     * ItemCollectionProvider constructor.
     * @param \Swissup\Ajaxsearch\Search\EngineResolverInterfaceFactory $engineResolverInterfaceFactory
     * @param array $factories
     */
    public function __construct(
        \Swissup\Ajaxsearch\Search\EngineResolverInterfaceFactory $engineResolverInterfaceFactory,
        array $factories
    ) {
        $this->engineResolverInterfaceFactory = $engineResolverInterfaceFactory;
        $this->factories = $factories;
    }

    /**
     * @inheritdoc
     */
    public function getCollection(\Magento\Catalog\Model\Category $category)
    {
        $currentSearchEngine = 'mysql';
        if ($this->engineResolverInterfaceFactory->isExist()) {
            /** @var \Magento\Framework\Search\EngineResolverInterface $engineResolver */
            $engineResolver = $this->engineResolverInterfaceFactory->create();
            $currentSearchEngine = $engineResolver->getCurrentSearchEngine();
        }

        if (!isset($this->factories[$currentSearchEngine])) {
            throw new \DomainException('Undefined factory ' . $currentSearchEngine);
        }
        $collection = $this->factories[$currentSearchEngine]->create();

        return $collection;
    }
}
