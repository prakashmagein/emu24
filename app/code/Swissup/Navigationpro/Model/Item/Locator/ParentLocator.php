<?php

namespace Swissup\Navigationpro\Model\Item\Locator;

use Swissup\Navigationpro\Model\Item;
use Magento\Framework\Exception\NotFoundException;
use Magento\Store\Model\Store;

class ParentLocator implements LocatorInterface
{
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
        \Magento\Framework\App\RequestInterface $request,
        \Swissup\Navigationpro\Model\ItemFactory $itemFactory
    ) {
        $this->request = $request;
        $this->itemFactory = $itemFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getItem()
    {
        if (null !== $this->item) {
            return $this->item;
        }

        if ($id = $this->request->getParam('parent_id')) {
            $item = $this->itemFactory->create();
            $item->setStoreId(
                $this->request->getParam('store', Store::DEFAULT_STORE_ID)
            );
            $item->load($id);
            return $this->item = $item;
        }

        return null;
    }
}
