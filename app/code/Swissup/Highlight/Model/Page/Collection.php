<?php

namespace Swissup\Highlight\Model\Page;

use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Collection extends \Magento\Framework\Data\Collection
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param EntityFactoryInterface $entityFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EntityFactoryInterface $entityFactory
    ) {
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($entityFactory);
    }

    /**
     * @inheritdoc
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        $data = $this->_getRawData();
        if (is_array($data)) {
            $pages = array_filter($data, function ($page) {
                return !empty($page['action_name']) && !empty($page['url']);
            });

            foreach ($pages as $pageType => $page) {
                $item = $this->getNewEmptyItem();
                $item->addData($page + ['type' => $pageType]);
                $this->addItem($item);
            }

            $this->_orderItems();
        }

        $this->_setIsLoaded();
        return $this;
    }

    /**
     * Order collection items after load
     *
     * @return $this
     */
    private function _orderItems()
    {
        $orders = $this->_orders;
        usort($this->_items, function ($itemA, $itemB) use ($orders) {
            $result = 0;
            foreach ($orders as $field => $direction) {
                $valueA = $itemA->getData($field);
                $valueB = $itemB->getData($field);
                if (!$direction || $valueA == $valueB) {
                    continue;
                }

                $result = $valueA > $valueB ? 1 : -1;
                if ($direction === \Magento\Framework\Data\Collection::SORT_ORDER_DESC) {
                    $result *= -1;
                }
            }

            return $result;
        });

        return $this;
    }

    /**
     * Get raw data about pages
     *
     * @return array
     */
    private function _getRawData()
    {
        return $this->_scopeConfig->getValue(
            'highlight',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
