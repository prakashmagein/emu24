<?php

namespace Swissup\Gdpr\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CookieGroup extends AbstractDb
{
    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_gdpr_cookie_group', 'group_id');
    }

    /**
     * Perform actions after object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->saveContent($object);

        return $this;
    }

    /**
     * Save content fields
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return void
     */
    private function saveContent(\Magento\Framework\Model\AbstractModel $object)
    {
        $connection = $this->getConnection();
        $table = $this->getTable('swissup_gdpr_cookie_group_content');

        $where = [
            'group_id = ?' => (int) $object->getId(),
            'store_id = ?' => (int) $object->getStoreId(),
        ];
        $connection->delete($table, $where);

        $data = [
            'group_id' => (int) $object->getId(),
            'store_id' => (int) $object->getStoreId(),
        ];
        $keys = [
            'title',
            'description',
        ];
        foreach ($keys as $key) {
            $data[$key] = $object->getData($key);
        }

        $connection->insert($table, $data);
    }
}
