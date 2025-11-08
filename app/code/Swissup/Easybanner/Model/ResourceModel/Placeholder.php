<?php
namespace Swissup\Easybanner\Model\ResourceModel;

/**
 * Placeholder mysql resource
 */
class Placeholder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_easybanner_placeholder', 'placeholder_id');
    }

    /**
      * Load an object using 'name' field if there's no field specified and
      * value is not numeric
      *
      * @param \Magento\Framework\Model\AbstractModel $object
      * @param mixed $value
      * @param string $field
      * @return $this
      */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && $field === null) {
            $field = 'name';
        }
        return parent::load($object, $value, $field);
    }

    /**
     * Get banner offset value for placeholder from table
     *
     * @param  string $placeholderId
     * @return string
     */
    public function getBannerOffset($placeholderId)
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable('swissup_easybanner_placeholder_offset'))
            ->where('placeholder_id=?', $placeholderId)
            ->order('id DESC')
            ->limit(1);
        $data = $this->getConnection()->fetchRow($select);
        if ($data) {
            return $data['banner_offset'];
        }

        return '0';
    }

    /**
     * Save banner offset in to table
     *
     * @param  string $placeholderId
     * @param  string $offset
     * @return $this
     */
    public function saveBannerOffset($placeholderId, $offset)
    {
        $this->getConnection()->insert(
            $this->getTable('swissup_easybanner_placeholder_offset'),
            [
                'placeholder_id' => $placeholderId,
                'banner_offset' => $offset
            ]
        );

        return $this;
    }

    /**
     * Condense placeholder_offset table (remove recordes with old offset state)
     *
     * @return $this
     */
    public function condenseBannerOffsetData()
    {
        $select = $this->getConnection()
            ->select()
            ->from(
                $this->getTable('swissup_easybanner_placeholder_offset'),
                ['id' => new \Zend_Db_Expr('MAX(`id`)')]
            )
            ->group(['placeholder_id']);
        $ids = $this->getConnection()->fetchAll($select);
        if (!empty($ids)) {
            $this->getConnection()
                ->delete(
                    $this->getTable('swissup_easybanner_placeholder_offset'),
                    ['`id` NOT IN (?)' => $ids]
                );
        }

        return $this;
    }
}
