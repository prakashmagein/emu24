<?php

namespace Swissup\Gdpr\Model\ResourceModel\CookieGroup;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\Store;

class CustomCollection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'group_id';

    /**
     * @var integer
     */
    protected $storeId = 0;

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Swissup\Gdpr\Model\CookieGroup::class,
            \Swissup\Gdpr\Model\ResourceModel\CookieGroup::class
        );
    }

    /**
     * Set store Id
     *
     * @param integer $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * Get store Id
     *
     * @return integer
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->addContentFieldsToResult();

        foreach ($this as $item) {
            if ($required = $item->getData('required')) {
                $item->setData('prechecked', $required);
            }
        }

        return parent::_afterLoad();
    }

    /**
     * Add scope-sensitive data for both default and current stores
     *
     * $return void
     */
    public function addContentFieldsToResult()
    {
        $ids = $this->getColumnValues('group_id');
        if (!count($ids)) {
            return;
        }

        $storeIds = [$this->getStoreId(), Store::DEFAULT_STORE_ID];

        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(['content' => $this->getTable('swissup_gdpr_cookie_group_content')])
            ->where('content.group_id IN (?)', $ids)
            ->where('content.store_id IN (?)', $storeIds);

        $result = $connection->fetchAll($select);
        if (!$result) {
            return;
        }

        $assocData = [];
        foreach ($result as $data) {
            if (!isset($assocData[$data['group_id']])) {
                $assocData[$data['group_id']]['content'] = [
                    'default' => [],
                    'scope' => [],
                ];
            }

            if ($data['store_id'] == Store::DEFAULT_STORE_ID) {
                $assocData[$data['group_id']]['content']['default'] = $data;
            } else {
                $assocData[$data['group_id']]['content']['scope'] = $data;
            }
        }

        foreach ($this as $item) {
            $itemId = $item->getId();
            if (!isset($assocData[$itemId])) {
                continue;
            }

            $item->addData($assocData[$itemId]);
            $item->addContentData(
                $assocData[$itemId]['content']['default'],
                $assocData[$itemId]['content']['scope']
            );
        }
    }

    /**
     * Add content fields for default store view. Used for grid filters.
     *
     * @return $this
     */
    public function addDescriptionToSelect()
    {
        $this->getSelect()->joinLeft(
            ['group_content' => $this->getTable('swissup_gdpr_cookie_group_content')],
            'main_table.cookie_id = group_content.cookie_id'
            . ' AND group_content.store_id = 0',
            ['title', 'description']
        );

        return $this;
    }
}
