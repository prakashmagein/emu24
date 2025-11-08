<?php

namespace Swissup\Gdpr\Model\ResourceModel\CookieGroup;

use Swissup\Gdpr\Model\ResourceModel\Traits\CollectionWithFilters;

class BuiltInCollection extends AbstractCollection
{
    use CollectionWithFilters;

    /**
     * Item object class name
     *
     * @var string
     */
    protected $_itemObjectClass = \Swissup\Gdpr\Model\CookieGroup::class;

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
        foreach (['required', 'prechecked'] as $key) {
            $data[$key] = $data[$key] ?? 0;
            $data[$key] = (string) (int) $data[$key]; // fix for ui renderers
        }

        if ($data['required']) {
            $data['prechecked'] = $data['required'];
        }

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
            'swissup_gdpr_cookie_groups_load_before',
            ['collection' => $this]
        );

        $this->_renderFilters();
        $this->_renderOrders();

        return $this;
    }
}
