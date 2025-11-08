<?php
namespace Swissup\Attributepages\Block\Widget\Attribute;

/**
 * Class attribute based pages list widget
 * @package Swissup\Attributepages\Block\Widget\Attribute
 */
class PagesList extends \Magento\Framework\View\Element\Template
     implements \Magento\Widget\Block\BlockInterface
{
    const DEFAULT_LIST_TEMPLATE = 'Swissup_Attributepages::attribute/list.phtml';
    const DEFAULT_WRAPPER_TEMPLATE = 'Swissup_Attributepages::attribute/block.phtml';

    protected function _prepareLayout()
    {
        $list = $this->getLayout()
            ->createBlock(
                'Swissup\Attributepages\Block\Attribute\PagesList',
                null,
                ['data' => $this->getData()]
            )
            ->setTemplate($this->getAttributeListTemplate());
        if ($this->getRemoveBlockWrapper()) {
            $this->setChild('attribute_list', $list);
        } else {
            $wrapper = $this->getLayout()
                ->createBlock(
                    'Magento\Framework\View\Element\Template',
                    null,
                    ['data' => $this->getData()]
                )
                ->setTemplate($this->getWrapperTemplate());
            $wrapper->setChild('children_list', $list);
            $this->setChild('attribute_list', $wrapper);
        }
        return parent::_prepareLayout();
    }
    public function getAttributeListTemplate()
    {
        $key = 'attribute_list_template';
        $template = $this->_getData($key);
        if (null === $template) {
            $template = self::DEFAULT_LIST_TEMPLATE;
            $this->setData($key, $template);
        }
        return $template;
    }
    public function getWrapperTemplate()
    {
        $key = 'wrapper_template';
        $template = $this->_getData($key);
        if (null === $template) {
            $template = self::DEFAULT_WRAPPER_TEMPLATE;
            $this->setData($key, $template);
        }
        return $template;
    }
    protected function _toHtml()
    {
        return $this->getChildHtml('attribute_list');
    }
}
