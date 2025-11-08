<?php
namespace Swissup\Suggestpage\Block;

use Magento\Framework\App\ObjectManager;
use Magento\Checkout\Block\Cart as CheckoutCart;

/**
 * Shopping cart block
 */
class Cart extends CheckoutCart
{
    protected $lastAddedQuoteItems;

    public function getItems()
    {
        if (null == $this->lastAddedQuoteItems) {
            $itemIds = $this->_checkoutSession->getSuggestpageQuoteItemIds();
            if (!is_array($itemIds)) {
                $itemIds = [];
            }
            $cartItems = parent::getItems();
            $items = [];

            foreach ($cartItems as $cartItem) {
                $cartItemId = $cartItem->getId();
                if (in_array($cartItemId, $itemIds)) {
                    $items[$cartItemId] = $cartItem;
                }
            }
            $this->lastAddedQuoteItems = $items;

            $registry = ObjectManager::getInstance()->get(
                \Magento\Framework\Registry::class
            );
            if ($this->lastAddedQuoteItems && !$registry->registry('product')) {
                $item = current($this->lastAddedQuoteItems);
                $registry->register('product', $item->getProduct());
            }
        }

        return $this->lastAddedQuoteItems;
    }
}
