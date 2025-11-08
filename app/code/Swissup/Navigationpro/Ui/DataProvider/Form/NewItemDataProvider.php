<?php

namespace Swissup\Navigationpro\Ui\DataProvider\Form;

class NewItemDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Swissup\Navigationpro\Model\Menu\Locator\LocatorInterface
     */
    protected $menuLocator;

    /**
     * @var \Swissup\Navigationpro\Model\Item\Locator\ParentLocator
     */
    protected $parentLocator;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param LocatorInterface $menuLocator
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Swissup\Navigationpro\Model\ResourceModel\Item\CollectionFactory $collectionFactory,
        \Swissup\Navigationpro\Model\Menu\Locator\LocatorInterface $menuLocator,
        \Swissup\Navigationpro\Model\Item\Locator\ParentLocator $parentLocator,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->menuLocator = $menuLocator;
        $this->parentLocator = $parentLocator;
    }

    public function getData()
    {
        return [
            null => [
                'menu_id'   => $this->menuLocator->getMenu()->getId(),
                'parent_id' => $this->parentLocator->getItem() ?
                    $this->parentLocator->getItem()->getId() : 0,
            ]
        ];
    }
}
