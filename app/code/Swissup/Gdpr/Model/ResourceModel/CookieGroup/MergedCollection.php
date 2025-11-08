<?php

namespace Swissup\Gdpr\Model\ResourceModel\CookieGroup;

use Swissup\Gdpr\Model\ResourceModel\Traits;

class MergedCollection extends AbstractCollection
{
    use Traits\CollectionWithFilters, Traits\MergedCollection;

    private $mergeKey = 'code';

    /**
     * @var array
     */
    private $collections;

    /**
     * @var string
     */
    protected $_itemObjectClass = \Swissup\Gdpr\Model\CookieGroup::class;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param BuiltInCollectionFactory $builtInCollection
     * @param CustomCollectionFactory $customCollection
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        BuiltInCollectionFactory $builtInCollection,
        CustomCollectionFactory $customCollection
    ) {
        parent::__construct($entityFactory);

        $this->collections = [
            $customCollection->create(),
            $builtInCollection->create(),
        ];
    }
}
