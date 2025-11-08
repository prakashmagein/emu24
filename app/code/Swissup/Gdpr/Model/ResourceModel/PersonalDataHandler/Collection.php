<?php

namespace Swissup\Gdpr\Model\ResourceModel\PersonalDataHandler;

class Collection implements \IteratorAggregate, \Countable
{
    /**
     * Collection items
     *
     * @var Swissup\Gdpr\Model\PersonalDataHandler\HandlerInterface[]
     */
    private $items = [];

    /**
     * Loading state flag
     *
     * @var bool
     */
    private $isLoaded;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Swissup\Gdpr\Model\PersonalDataHandler\Gdpr
     */
    private $gdprHandler;

    private $handlers;

    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Swissup\Gdpr\Model\PersonalDataHandler\GdprFactory $gdprHandler,
        array $handlers = []
    ) {
        $this->eventManager = $eventManager;
        $this->gdprHandler = $gdprHandler->create();
        $this->handlers = $handlers;
    }

    /**
     * Load data
     *
     * @return $this
     */
    public function loadData()
    {
        if ($this->isLoaded()) {
            return $this;
        }

        foreach ($this->handlers as $handler) {
            $this->addItem($handler);
        }

        $this->eventManager->dispatch(
            'swissup_gdpr_personal_data_handlers_load_before',
            ['collection' => $this]
        );

        // Must be the last item, as it will anonymize client's request
        $this->addItem($this->gdprHandler);

        $this->setIsLoaded(true);

        return $this;
    }

    /**
     * Adding item to item array
     *
     * @param   \Magento\Framework\DataObject $item
     * @return $this
     * @throws \Exception
     */
    public function addItem($item)
    {
        $this->items[] = $item;
        return $this;
    }

    /**
     * Remove item from collection by item key
     *
     * @param   mixed $key
     * @return $this
     */
    public function removeItemByKey($key)
    {
        if (isset($this->items[$key])) {
            unset($this->items[$key]);
        }
        return $this;
    }

    /**
     * Retrieve collection loading status
     *
     * @return bool
     */
    public function isLoaded()
    {
        return $this->isLoaded;
    }

    /**
     * Set collection loading status flag
     *
     * @param bool $flag
     * @return $this
     */
    private function setIsLoaded($flag = true)
    {
        $this->isLoaded = $flag;
        return $this;
    }

    /**
     * Implementation of \IteratorAggregate::getIterator()
     *
     * @return \ArrayIterator
     */
    public function getIterator(): \Traversable
    {
        $this->loadData();
        return new \ArrayIterator($this->items);
    }

    /**
     * Retrieve count of collection loaded items
     *
     * @return int
     */
    public function count(): int
    {
        $this->loadData();
        return count($this->items);
    }
}
