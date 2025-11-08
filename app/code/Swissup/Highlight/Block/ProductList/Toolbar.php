<?php

namespace Swissup\Highlight\Block\ProductList;

use Swissup\Highlight\Model\Config\Source\PaginationType;

class Toolbar extends \Magento\Catalog\Block\Product\ProductList\Toolbar
{
    /**
     * Set collection to pager
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        // sort by column, alias, etc
        if ($this->getRawOrder()) {
            $this->setSkipOrder(true);
            $collection->getSelect()->order($this->getRawOrder());
        }

        if ($this->getCurrentOrder() === 'popularity') {
            $this->setSkipOrder(true);
            $collection->getSelect()->order('popularity DESC');
        }

        $currentOrder = $this->getData('_current_grid_order');
        $currentDir = strtoupper((string)$this->getData('_current_grid_direction'));
        if (!in_array($currentDir, ['ASC', 'DESC'])) {
            $currentDir = 'ASC';
        }

        if ($this->getSkipOrder()) {
            // prevent order in parent method
            $this->setData('_current_grid_order', false);
        }

        $return = parent::setCollection($collection);

        $from = $collection->getSelect()->getPart('from');
        if ($currentOrder === 'position' && isset($from['cat_index'])) {
            $collection->getSelect()->reset('order')->order([
                'cat_index.position ' . $currentDir,
                'cat_index.product_id DESC',
            ]);
        } elseif ($currentOrder === 'price' && isset($from['price_index'])) {
            $collection->getSelect()->reset('order')->order([
                'price_index.min_price ' . $currentDir,
                'price_index.entity_id DESC',
            ]);
        } elseif ($currentOrder === 'entity_id' && isset($from['cat_index'])) {
            $collection->getSelect()->reset('order')->order([
                'cat_index.product_id DESC',
            ]);
        } else {
            // Add order by id to guarantee different products per page
            $collection->addOrder('entity_id', 'DESC');
        }

        // restore original value
        if ($this->getSkipOrder()) {
            $this->setData('_current_grid_order', $currentOrder);
        }

        return $return;
    }

    public function hasMorePages(): bool
    {
        if (!$this->hasData('has_more_pages')) {
            $this->setData('has_more_pages', (bool) $this->getMorePagesCount());
        }
        return $this->getData('has_more_pages');
    }

    public function getMorePagesCount(): int
    {
        if (!$this->hasData('more_pages_count')) {
            $maxPage = (int) $this->getMaxPageCount();
            $curPage = $this->getCollection()->getCurPage() ?: 1;
            $pageSize = $this->getCollection()->getPageSize();

            if ($maxPage > 1 && $curPage >= $maxPage || !$pageSize) {
                return 0;
            }

            if (!$this->getCollection()->isLoaded()) {
                $this->getCollection()->load();
            }

            $select = clone $this->getCollection()->getSelect();
            $select->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
            $select->limit($pageSize * 3, $pageSize * $curPage);
            $count = count($this->getCollection()->getConnection()->fetchAll($select));
            $morePages = ceil($count / $pageSize);

            if ($maxPage > 1 && $maxPage < $morePages + $curPage) {
                $morePages = $maxPage - $curPage;
            }

            $this->setData('more_pages_count', $morePages);
        }
        return $this->getData('more_pages_count');
    }

    public function getPagerHtml()
    {
        $pagerBlock = $this->getChildBlock('product_list_toolbar_pager');
        if ($pagerBlock && $this->getPaginationType() === PaginationType::TYPE_IMPROVED) {
            $pagerBlock
                ->setTemplate('Swissup_Highlight::product/list/toolbar-improved-pager.phtml')
                ->setFramePagesOptimized(range(
                    max(1, $this->getCurrentPage() - 3),
                    $this->getCurrentPage() + $this->getMorePagesCount()
                ))
                ->setHasMorePages($this->hasMorePages());
        }

        return parent::getPagerHtml();
    }

    /**
     * Return current page from request
     *
     * @return int
     */
    public function getCurrentPage()
    {
        $page = $this->_getData('_current_page');
        if ($page) {
            return $page;
        }
        return parent::getCurrentPage();
    }

    /**
     * Get grit products sort order field
     *
     * @return string
     */
    public function getCurrentOrder()
    {
        $order = $this->_getData('_current_grid_order');
        if (false === $order || $order) {
            // ability to disable sort for random products widget
            return $order;
        }
        return parent::getCurrentOrder();
    }

    /**
     * Rewritten to change default sort order on highlight pages
     *
     * @param array $customOptions Optional parameter for passing custom selectors from template
     * @return string
     */
    public function getWidgetOptionsJson(array $customOptions = [])
    {
        if ($this->_orderField) {
            $customOptions['orderDefault'] = $this->_orderField;
        }
        return parent::getWidgetOptionsJson($customOptions);
    }

    protected function _toHtml()
    {
        if ($this->getIsBottom() && $this->getHidePagination()) {
            return '';
        }
        return parent::_toHtml();
    }

    public function getTemplate()
    {
        switch ($this->getPaginationType()) {
            case PaginationType::TYPE_IMPROVED:
                return 'Swissup_Highlight::product/list/toolbar-improved.phtml';
            default:
                return parent::getTemplate();
        }
    }

    public function fetchView($fileName)
    {
        if ($this->getHideLimiter() && strpos($fileName, '/limiter.phtml') !== false) {
            return '';
        }
        return parent::fetchView($fileName);
    }
}
