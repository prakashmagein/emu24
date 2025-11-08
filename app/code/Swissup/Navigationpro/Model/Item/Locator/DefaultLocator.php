<?php

namespace Swissup\Navigationpro\Model\Item\Locator;

use Swissup\Navigationpro\Model\Item;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Registry;
use Magento\Store\Model\Store;

class DefaultLocator implements LocatorInterface
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var \Swissup\Navigationpro\Model\Item
     */
    private $item;

    /**
     * @param Registry $registry
     */
    public function __construct(
        Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        \Swissup\Navigationpro\Model\ItemFactory $itemFactory
    ) {
        $this->registry = $registry;
        $this->request = $request;
        $this->itemFactory = $itemFactory;
    }

    /**
     * {@inheritdoc}
     * @throws NotFoundException
     */
    public function getItem()
    {
        if (null !== $this->item) {
            return $this->item;
        }

        if ($item = $this->registry->registry('navigationpro_item')) {
            return $this->item = $item;
        }

        if ($id = $this->request->getParam('item_id')) {
            $item = $this->itemFactory->create();
            $item->setStoreId(
                $this->request->getParam('store', Store::DEFAULT_STORE_ID)
            );
            $item->load($id);
            return $this->item = $item;
        }

        throw new NotFoundException(__('Item was not registered'));
    }
}
