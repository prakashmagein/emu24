<?php

namespace Swissup\Fblike\Block\Product;

use Magento\Framework\View\Element\Template;

class Button extends \Swissup\Fblike\Block\Button
{
    private $_coreRegistry;
    /**
     * @param Template\Context $context
     * @param \Magento\Framework\Registry $registry,
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigSection()
    {
        return 'product';
    }
}
