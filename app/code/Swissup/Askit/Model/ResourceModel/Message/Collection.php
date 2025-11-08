<?php
namespace Swissup\Askit\Model\ResourceModel\Message;

use Swissup\Askit\Api\Data\MessageInterface;
use Magento\Framework\Api;
//use Magento\Framework\DB\Select;
use Laminas\Db\Sql\Select;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection implements Api\Search\SearchResultInterface
{
    /**
     * @var Api\Search\AggregationInterface
     */
    protected $aggregations;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaInterface
     */
    protected $searchCriteria;

    /**
     * @var int
     */
    protected $totalCount;

    /**
     *
     * @var boolean|array
     */
    protected $itemInfo = false;

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Swissup\Askit\Model\Message::class,
            \Swissup\Askit\Model\ResourceModel\Message::class
        );
    }

    /**
     *
     * @param array|int $storeId
     */
    public function addStoreFilter($storeId)
    {
        if (!is_array($storeId)) {
            $storeId = [$storeId];
        }
        $this->getSelect()
            ->where('main_table.store_id IN (?)', $storeId);
        return $this;
    }

    /**
     *
     * @param array|int $status
     */
    public function addStatusFilter($status = MessageInterface::STATUS_APPROVED)
    {
        if (!is_array($status)) {
            $status = [$status];
        }
        $this->addFieldToFilter('status', ['in' => $status]);
        return $this;
    }

    /**
     *
     * @param int $customerId
     */
    public function addPrivateFilter($customerId = null)
    {
        if ($customerId) {
            $this->addFieldToFilter(['is_private', 'customer_id'], ['0', $customerId]);
        } else {
            $this->addFilter('is_private', '0');
        }

        return $this;
    }

    /**
     * Initialize system messages after load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        $this->addFirstItemData();
        return $this;
    }

    /**
     *
     * @return $this
     */
    public function addFirstItemData()
    {
        $select = $this->getConnection()->select()
            ->from(
                ['main_table' => $this->getTable('swissup_askit_item')],
                ['message_id', 'item_id', 'item_type_id']
            );
        $data = $this->getConnection()->fetchAssoc($select);
        $keys = ['item_id', 'item_type_id'];
        foreach ($this->_items as $messageId => $item) {
            foreach ($keys as $key) {
                if (isset($data[$messageId][$key])) {
                    $value = (int) $data[$messageId][$key];
                    $item->setData($key, $value);
                }
            }
        }
        return $this;
    }

    /**
     *
     * @return $this
     */
    public function joinItemTable()
    {
        if (false === $this->itemInfo) {
            $keys = ['item_id', 'item_type_id'];
            $table = $this->getTable('swissup_askit_item');
            $this->getSelect()->joinLeft(
                ['i' => $table],
                'i.message_id = main_table.id',
                $keys
            )
            ->group('main_table.id');
            $this->itemInfo = true;
        }
        return $this;
    }

    /**
     *
     * @param int $id
     * @return $this
     */
    public function addProductFilter($id)
    {
        $this->joinItemTable();
        $this->getSelect()
            ->where('i.item_type_id = ?', MessageInterface::TYPE_CATALOG_PRODUCT)
            ->where('i.item_id = ?', $id);
        return $this;
    }

    /**
     *
     * @param int $id
     */
    public function addCategoryFilter($id)
    {
        $this->joinItemTable();
        $this->getSelect()
            ->where('i.item_type_id = ?', MessageInterface::TYPE_CATALOG_CATEGORY)
            ->where('i.item_id = ?', $id);
        return $this;
    }

    /**
     *
     * @param int $id
     * @return $this
     */
    public function addPageFilter($id)
    {
        $this->joinItemTable();
        $this->getSelect()
            ->where('i.item_type_id = ?', MessageInterface::TYPE_CMS_PAGE)
            ->where('i.item_id = ?', $id);
        return $this;
    }

    /**
     *
     * @param int $id
     * @return $this
     */
    public function addIdFilter($id)
    {
        $this->getSelect()
            ->where('main_table.id = ?', $id);
        return $this;
    }

    /**
     *
     * @param int $questionId
     * @return $this
     */
    public function addQuestionFilter($questionId)
    {
        $this->getSelect()
            ->where('main_table.parent_id = ?', $questionId);
        return $this;
    }

    /**
     *
     * @return $this
     */
    public function addAnswerFilter()
    {
        $this->getSelect()
            ->where('main_table.parent_id IS NOT NULL')
            ->where('main_table.parent_id <> ?', 0);
        return $this;
    }

    /**
     *
     * @param int $customerId
     * @return $this
     */
    public function addCustomerFilter($customerId)
    {
        $this->getSelect()
            ->where('main_table.customer_id = ?', $customerId);
        return $this;
    }

    /**
     *
     * @param string $order
     * @return $this
     */
    public function addHintOrder($order = Select::ORDER_DESCENDING)
    {
        if (!in_array($order, [Select::ORDER_ASCENDING, Select::ORDER_DESCENDING])) {
            $order = Select::ORDER_DESCENDING;
        }
        $this->getSelect()
            ->order('main_table.hint ' . $order);
        return $this;
    }

    /**
     *
     * @param string $order
     * @return $this
     */
    public function addCreatedTimeOrder($order = Select::ORDER_DESCENDING)
    {
        if (!in_array($order, [Select::ORDER_ASCENDING, Select::ORDER_DESCENDING])) {
            $order = Select::ORDER_DESCENDING;
        }
        $this->getSelect()
            ->order('main_table.created_time ' . $order);
        return $this;
    }

    /* Implements \Magento\Framework\Api\Search\SearchResultInterface*/
    /**
     * @return \Magento\Framework\Api\Search\AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * @param \Magento\Framework\Api\Search\AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
        return $this;
    }

    /**
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return $this->searchCriteria;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $this->searchCriteria = $searchCriteria;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        if (!$this->totalCount) {
            $this->load();
            $this->totalCount = $this->getSize();
        }
        return $this->totalCount;
    }

    /**
     * @param int $totalCount
     * @return $this
     */
    public function setTotalCount($totalCount)
    {
        $this->totalCount = $totalCount;
        return $this;
    }

    /**
     * Set items list.
     *
     * @param \Swissup\Askit\Model\Message[] $items
     * @return $this
     */
    public function setItems(?array $items = null)
    {
        if ($items) {
            foreach ($items as $item) {
                $this->addItem($item);
            }
            unset($this->totalCount);
        }
        return $this;
    }
}
