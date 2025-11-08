<?php
namespace Swissup\Easybanner\Model\ResourceModel\Handle;

use Magento\Framework\Data\Collection\EntityFactoryInterface;

class Collection extends \Magento\Framework\Data\Collection
{
    private $_handles = [];

    private $_filterIncrement = 0;

    private $pageTypeConfig;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        \Magento\Framework\View\Layout\PageType\Config $config
    ) {
        parent::__construct($entityFactory);
        $this->pageTypeConfig = $config;
    }

    public function getHandles()
    {
        foreach ($this->pageTypeConfig->getPageTypes() as $page) {
            $result[$page->getId()] = [
                'id' => $page->getId(),
            ];
        }

        ksort($result);

        $this->_handles = $result;
    }

    /**
     * Load data
     *
     * @return \Swissup\Easybanner\Model\ResourceModel\Handle_Collection
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        $this->getHandles();
        $this->_filterAndSort();
        $this->_totalRecords = count($this->_handles);
        $this->_setIsLoaded();

        $from = ($this->getCurPage() - 1) * $this->getPageSize();
        $to = $from + $this->getPageSize() - 1;
        $cnt = 0;

        foreach ($this->_handles as $row) {
            $cnt++;
            if ($cnt < $from || $cnt > $to) {
                continue;
            }
            $item = new \Magento\Framework\DataObject();
            $item->addData($row);
            $this->addItem($item);
        }

        return $this;
    }

    /**
     * With specified collected items:
     *  - apply filters
     *  - sort
     *
     * @return void
     */
    private function _filterAndSort()
    {
        if (!empty($this->_filters)) {
            foreach ($this->_handles as $key => $row) {
                foreach ($this->_filters as $filter) {
                    $method = $filter['callback'];
                    if (!$this->$method($filter['field'], $filter['value'], $row)) {
                        unset($this->_handles[$key]);
                    }
                }
            }
        }

        if (!empty($this->_orders)) {
            foreach ($this->_orders as $key => $direction) {
                if (self::SORT_ORDER_ASC === strtoupper($direction)) {
                    asort($this->_handles);
                } else {
                    arsort($this->_handles);
                }
                break;
            }
        }
    }

    /**
     * Set select order
     * Currently supports only sorting by one column
     *
     * @param   string $field
     * @param   string $direction
     * @return  \Swissup\Easybanner\Model\ResourceModel\Handle_Collection
     */
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        $this->_orders = [$field => $direction];
        return $this;
    }

    /**
     * Fancy field filter
     *
     * @param string $field
     * @param mixed $cond
     * @see Varien_Data_Collection_Db::addFieldToFilter()
     * @return \Swissup\Easybanner\Model\ResourceModel\Handle_Collection
     */
    public function addFieldToFilter($field, $cond)
    {
        if (isset($cond['like'])) {
            return $this->addCallbackFilter($field, $cond['like'], 'filterCallbackLike');
        }
        return $this;
    }

    /**
     * Set a custom filter with callback
     * The callback must take 3 params:
     *     string $field       - field key,
     *     mixed  $filterValue - value to filter by,
     *     array  $row         - a generated row (before generaring varien objects)
     *
     * @param string $field
     * @param mixed $value
     * @param string $callback
     * @return \Swissup\Easybanner\Model\ResourceModel\Handle_Collection
     */
    public function addCallbackFilter($field, $value, $callback)
    {
        $this->_filters[$this->_filterIncrement] = [
            'field'       => $field,
            'value'       => $value,
            'callback'    => $callback
        ];
        $this->_filterIncrement++;
        return $this;
    }

    /**
     * Callback method for 'like' fancy filter
     *
     * @param string $field
     * @param mixed $filterValue
     * @param array $row
     * @return bool
     * @see addFieldToFilter()
     * @see addCallbackFilter()
     */
    public function filterCallbackLike($field, $filterValue, $row)
    {
        // undo all of Magento\Framework\DB\Helper@escapeLikeValue logic,
        // because we don't search in DB
        // escapeLikeValue is applied automatically in
        // Magento\Backend\Block\Widget\Grid\Column\Filter@getCondition
        $filterValue = trim($filterValue, "'");
        $filterValue = str_replace("\\", '', $filterValue);
        $filterValue = str_replace("%", '', $filterValue);

        return strpos($row[$field], $filterValue) !== false;
    }
}
