<?php

namespace Swissup\Navigationpro\Ui\DataProvider\Form;

class MassEditItemDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Swissup\Navigationpro\Model\Menu\Locator\LocatorInterface
     */
    protected $menuLocator;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

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
        \Magento\Framework\App\RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->menuLocator = $menuLocator;
        $this->request = $request;
    }

    public function getData()
    {
        return [
            null => [
                'menu_id'   => $this->menuLocator->getMenu()->getId(),
                'store_id' => $this->request->getParam('store_id'),
                'store_ids' => [(string)(int)$this->request->getParam('store_id')],
            ]
        ];
    }
}
