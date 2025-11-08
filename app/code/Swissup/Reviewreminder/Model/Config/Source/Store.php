<?php
namespace Swissup\Reviewreminder\Model\Config\Source;

class Store implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    /**
     * @param \Magento\Store\Model\System\Store $systemStore
     */
    public function __construct(
        \Magento\Store\Model\System\Store $systemStore
    ) {
        $this->_systemStore = $systemStore;
    }
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_systemStore->getStoreValuesForForm(false, true);
    }
}
