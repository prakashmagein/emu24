<?php
namespace Swissup\Ajaxsearch\Model\Query;

/**
 * Factory class for @see \Magento\Search\Model\ResourceModel\Query\Collection
 */
class CollectionFactory extends \Magento\Search\Model\ResourceModel\Query\CollectionFactory
{
    /**
     * @var string
     */
    protected $_instanceName;

    /**
     *
     * @param string $instanceName
     */
    public function setInstanceName($instanceName)
    {
        $this->_instanceName = $instanceName;
        return $this;
    }
}
