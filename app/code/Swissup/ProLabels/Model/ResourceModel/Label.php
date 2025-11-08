<?php

namespace Swissup\ProLabels\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * ProLabels Label mysql resource
 */
class Label extends \Magento\Rule\Model\ResourceModel\AbstractResource
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_prolabels_label', 'label_id');
    }

    public function getIndexedProducts($id)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('swissup_prolabels_index'),
            'entity_id'
        )->where(
            'label_id = :label_id'
        );
        $binds = [':label_id' => (int)$id];
        return $connection->fetchCol($select, $binds);
    }

    public function getProductLabels(
        $productId,
        $storeId = 0,
        $customerGroupId = 0,
        $mode = 'product'
    ) {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('swissup_prolabels_index'),
            ['entity_id', 'label_id']
        );
        $select->where('entity_id IN (?)', $productId);

        $records = $connection->fetchAll($select);
        $labelIds = array_unique(array_column($records, 'label_id'));
        $labelsData = $this->getLabelDetails($labelIds, $storeId, $customerGroupId, $mode);

        $labels = [];
        foreach ($records as $data) {
            $data = array_merge($data, $labelsData[$data['label_id']] ?? []);
            if (empty($data['text']) && empty($data['custom']) && empty($data['image'])) {
                continue;
            }
            $productId = $data['entity_id'];
            unset($data['entity_id']);
            $labels[$productId][] = new \Magento\Framework\DataObject($data);
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
            $this->getMainTable(),
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
        $select->where('status = 1');
        $select->where('label_id IN (?)', $labelIds);
        $select->where('store_id LIKE \'%\\"0\\"%\' OR store_id LIKE ?', "%\"{$storeId}\"%");
        $select->where('customer_groups LIKE ?', "%\"{$customerGroupId}\"%");
        return $connection->fetchAssoc($select);
    }

    /**
     * @param  AbstractModel $label
     * @return AbstractModel
     */
    public function duplicate(AbstractModel $label)
    {
        $newLabel = clone $label;
        $this->saveNewObject($newLabel);

        return $newLabel;
    }
}
