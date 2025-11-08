<?php

namespace Swissup\ProLabelsConfigurableProduct\Model\ResourceModel\Label;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Configurable extends AbstractDb
{
    /**
     * @var bool
     */
    protected $_isPkAutoIncrement = false;

    /**
     * Init resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_prolabels_label_configurable', 'label_id');
    }

    public function getChildLabels(
        $parentId,
        $storeId = 0,
        $customerGroupId = 0,
        $mode = 'product'
    ) {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->distinct()
            ->from(
                ['s' => $this->getTable('catalog_product_super_link')],
                ['s.parent_id']
            )
            ->join(
                ['i' => $this->getTable('swissup_prolabels_index')],
                'i.entity_id = s.product_id',
                ['i.label_id']
            )
            ->where('s.parent_id IN (?)', $parentId);

        $records = $connection->fetchAll($select);
        $labelIds = array_unique(array_column($records, 'label_id'));
        $labelsData = $this->getLabelDetails($labelIds, $storeId, $customerGroupId, $mode);

        $labels = [];
        foreach ($records as $data) {
            $data = array_merge($data, $labelsData[$data['label_id']] ?? []);
            if (empty($data['text']) && empty($data['custom']) && empty($data['image'])) {
                continue;
            }
            $parentId = $data['parent_id'];
            unset($data['parent_id']);
            $labels[$parentId][] = new \Magento\Framework\DataObject($data);
        }

        return $labels;
    }

    private function getLabelDetails(
        $labelIds,
        $storeId = 0,
        $customerGroupId = 0,
        $mode = 'product'
    ) {
        if (!$labelIds) {
            return [];
        }

        $connection = $this->getConnection();
        $select = $connection->select()->from(
            ['l' => $this->getTable('swissup_prolabels_label')],
            [
                'label_id' => 'label_id',
                'sort_order' => "sort_order",
                'position' => "{$mode}_position",
                'text' => "{$mode}_text",
                'custom' => "{$mode}_custom_style",
                'custom_url' => "{$mode}_custom_url",
                'round_method' => "{$mode}_round_method",
                'round_value' => "{$mode}_round_value",
                'image' => "{$mode}_image",
                'target_element' => "{$mode}_target_element",
                'insert_method' => "{$mode}_insert_method"
            ]
        );
        $select->join(
            ['c' => $this->getMainTable()],
            'c.label_id = l.label_id',
            []
        );
        $select->where('status = 1');
        $select->where("{$mode}_show_child_labels = 1");
        $select->where('l.label_id IN (?)', $labelIds);
        $select->where('store_id LIKE \'%\\"0\\"%\' OR store_id LIKE ?', "%\"{$storeId}\"%");
        $select->where('customer_groups LIKE ?', "%\"{$customerGroupId}\"%");
        return $connection->fetchAssoc($select);
    }

    public function read($labelId): array
    {
        $connection = $this->getConnection();
        $select = $this->_getLoadSelect('label_id', $labelId, null);

        return $connection->fetchAssoc($select) ?: [];
    }

    public function write($object): void
    {
        $data = $this->_prepareDataForSave($object);
        $fieldsToUpdate = array_keys($data);
        $this->getConnection()->insertOnDuplicate(
            $this->getMainTable(),
            $data,
            $fieldsToUpdate
        );
    }
}
