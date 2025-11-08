<?php

namespace Swissup\Gdpr\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Cookie extends AbstractDb
{
    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_gdpr_cookie', 'cookie_id');
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
        $table = $this->getTable('swissup_gdpr_cookie_content');

        $where = [
            'cookie_id = ?' => (int) $object->getId(),
            'store_id = ?' => (int) $object->getStoreId(),
        ];
        $connection->delete($table, $where);

        if ($object->getDescription() === null) {
            return;
        }

        $connection->insert($table, [
            'cookie_id' => (int) $object->getId(),
            'store_id' => (int) $object->getStoreId(),
            'description' => (string) $object->getDescription(),
        ]);
    }
}
