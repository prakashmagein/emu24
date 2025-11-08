<?php
namespace Swissup\EasySlide\Model\ResourceModel;

/**
 * Easyslide Slides mysql resource
 */
class Slides extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Swissup\EasySlide\Helper\Image
     */
    protected $helper;

    /**
     * Constructor
     *
     * @param \Swissup\EasySlide\Helper\Image                   $helper
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string                                            $connectionName
     */
    public function __construct(
        \Swissup\EasySlide\Helper\Image $helper,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ) {
        $this->helper = $helper;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_easyslide_slides', 'slide_id');
    }

    public function getSlides($sliderId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable())
            ->where('slider_id = ?', $sliderId)
            ->order('sort_order');

        return $connection->fetchAll($select);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->helper->cleanCached($object->getImage());
        $this->helper->delete($object->getImage());
        return parent::_afterDelete($object);
    }
}
