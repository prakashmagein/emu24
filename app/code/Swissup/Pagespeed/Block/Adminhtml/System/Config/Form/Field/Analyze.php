<?php

namespace Swissup\Pagespeed\Block\Adminhtml\System\Config\Form\Field;

use Swissup\Pagespeed\Block\Adminhtml\System\Config\Form\Field\StoreAbstract as Field;
    // Magento\Config\Block\System\Config\Form\Field
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Analyze extends Field
{
    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    private $localeResolver;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\Resolver $localeResolver
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\Resolver $localeResolver,
        array $data = []
    ) {
        parent::__construct($context, $storeManager, $data);
        $this->localeResolver = $localeResolver;
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        /** @var \Magento\Backend\Block\Template $block */
        $block = $this->getLayout()->createBlock(\Magento\Backend\Block\Template::class);

        $block->setTemplate('Swissup_Pagespeed::analyze.phtml')
            ->setStoreBaseUrl($this->getStoreBaseUrl())
            // ->setStoreBaseUrl('https://swissupdemo.com/pagespeed/current/')
            ->setLocale($this->localeResolver->getLocale());

        return $block->toHtml();
    }
}
