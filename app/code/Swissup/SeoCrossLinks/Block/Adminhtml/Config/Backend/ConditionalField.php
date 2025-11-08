<?php

namespace Swissup\SeoCrossLinks\Block\Adminhtml\Config\Backend;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ConditionalField extends Field
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        if (!$this->isMagefanPostAvailable()) {            
            return '';
        }

        return parent::_getElementHtml($element);
    }

    protected function isMagefanPostAvailable()
    {
        return class_exists(\Magefan\Blog\Helper\Data::class)
            && $this->scopeConfig->isSetFlag('mfblog/general/enabled');
    }
}
