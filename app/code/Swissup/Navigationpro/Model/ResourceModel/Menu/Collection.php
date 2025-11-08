<?php

namespace Swissup\Navigationpro\Model\ResourceModel\Menu;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'menu_id';

    /**
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    private $configValueFactory;

    /**
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    private $canAddConfigScopes;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        ?\Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        ?\Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->configValueFactory = $configValueFactory;
    }

    protected function _construct()
    {
        $this->_init(
            'Swissup\Navigationpro\Model\Menu',
            'Swissup\Navigationpro\Model\ResourceModel\Menu'
        );
    }

    /**
     * Allow to add config_scopes where menu replaces standard top menu items
     * @param boolean $flag
     */
    public function setCanAddConfigScopes($flag = true)
    {
        $this->canAddConfigScopes = $flag;
    }

    /**
     * @return boolean
     */
    public function getCanAddConfigScopes()
    {
        return $this->canAddConfigScopes;
    }

    /**
     * Add config scope ids to each menu
     *
     * @return void
     */
    protected function addConfigScopes()
    {
        $config = $this->configValueFactory->create()->getCollection()
            ->addFieldToFilter('path', \Swissup\Navigationpro\Helper\Data::CONFIG_PATH_TOPMENU)
            ->addFieldToFilter('value', $this->getColumnValues('identifier'));

        foreach ($this as $menu) {
            $menu->importConfigScopes($config);
        }
    }

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        if ($this->getCanAddConfigScopes()) {
            $this->addConfigScopes();
        }
        return parent::_afterLoad();
    }
}
