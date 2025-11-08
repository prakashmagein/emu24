<?php

namespace Swissup\Gdpr\Model\ResourceModel\Cookie;

use Swissup\Gdpr\Model\ResourceModel\Traits\CollectionWithFilters;

class BuiltInCollection extends AbstractCollection
{
    use CollectionWithFilters;

    /**
     * Item object class name
     *
     * @var string
     */
    protected $_itemObjectClass = \Swissup\Gdpr\Model\Cookie::class;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        parent::__construct($entityFactory);

        $this->eventManager = $eventManager;
    }

    /**
     * Add an object to the collection
     *
     * @param array $data
     * @return $this
     */
    public function addItemFromArray(array $data)
    {
        if (!isset($data['status'])) {
            $data['status'] = \Swissup\Gdpr\Model\Cookie::STATUS_ENABLED;
        }
        $data['status'] = (string) (int) $data['status']; // fix for ui 'toggle' component

        $item = $this->getNewEmptyItem();
        $item->setData($data);
        return $this->addItem($item);
    }

    /**
     * Load data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        $this->_setIsLoaded(true);

        $this->eventManager->dispatch(
            'swissup_gdpr_cookies_load_before',
            ['collection' => $this]
        );

        $this->_renderFilters();
        $this->_renderOrders();

        return $this;
    }
}
