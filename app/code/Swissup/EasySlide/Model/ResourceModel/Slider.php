<?php
namespace Swissup\EasySlide\Model\ResourceModel;

/**
 * Easyslide Slider mysql resource
 */
class Slider extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Swissup\EasySlide\Model\SlidesFactory
     */
    protected $slidesFactory;

    /**
     * @var \Swissup\EasySlide\Model\ResourceModel\Slides\CollectionFactory
     */
    protected $slidesCollectionFactory;

    /**
     * {@inheritdoc}
     */
    protected $_serializableFields = ['slider_config' => [null, []]];

    /**
     * Constructor
     *
     * @param \Swissup\EasySlide\Model\SlidesFactory            $slidesFactory
     * @param \Swissup\EasySlide\Model\ResourceModel\Slides\CollectionFactory $slidesCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string                                            $connectionName
     */
    public function __construct(
        \Swissup\EasySlide\Model\SlidesFactory $slidesFactory,
        \Swissup\EasySlide\Model\ResourceModel\Slides\CollectionFactory $slidesCollectionFactory,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ) {
        $this->slidesFactory = $slidesFactory;
        $this->slidesCollectionFactory = $slidesCollectionFactory;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_easyslide_slider', 'slider_id');
    }

    public function getSlides($sliderId)
    {
        $collection = $this->getSlidesCollection($sliderId);
        $collection
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter(
                ['active_to_timestamp', 'active_to_timestamp'],
                [['null' => true], ['gt' => time()]]
            )
            ->addOrder('sort_order', $collection::SORT_ORDER_ASC);

        return $collection->getData();
    }

    public function getSlidesCollection($sliderId)
    {
        return $this->slidesCollectionFactory->create()
            ->addFieldToFilter('slider_id', $sliderId);
    }

    public function getOptionSliders()
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable())
        ->where('is_active = ?', 1);

        return $connection->fetchAll($select);
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $slide = $this->slidesFactory->create();
        $slidesArray = $slide->getSlides($object->getId());
        foreach ($slidesArray as $slideItem) {
            // Delete each slide individually to remove imgae files.
            $slide->load($slideItem['slide_id'])->delete();
        }

        return parent::_beforeDelete($object);
    }
}
