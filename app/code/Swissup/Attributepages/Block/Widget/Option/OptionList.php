<?php
namespace Swissup\Attributepages\Block\Widget\Option;

/**
 * Class option list widget
 * @package Swissup\Attributepages\Block\Widget\Option
 */
class OptionList extends \Magento\Framework\View\Element\Template
     implements \Magento\Widget\Block\BlockInterface
{
    const DEFAULT_LIST_TEMPLATE = 'Swissup_Attributepages::option/list.phtml';
    const DEFAULT_SLIDER_TEMPLATE = 'Swissup_Attributepages::option/slider.phtml';
    const DEFAULT_WRAPPER_TEMPLATE = 'Swissup_Attributepages::attribute/block.phtml';

    public function getOptionListTemplate()
    {
        $key = 'option_list_template';
        $template = $this->_getData($key);
        if (null === $template) {
            if ($this->getIsSlider()) {
                $template = self::DEFAULT_SLIDER_TEMPLATE;
            } else {
                $template = self::DEFAULT_LIST_TEMPLATE;
            }
            $this->setData($key, $template);
        }

        return $template;
    }

    public function getAttributeBlockTemplate()
    {
        $key = 'attribute_block_template';
        $template = $this->_getData($key);
        if (null === $template) {
            $template = self::DEFAULT_WRAPPER_TEMPLATE;
            $this->setData($key, $template);
        }

        return $template;
    }

    protected function _toHtml()
    {
        if (null === $this->_getData('group_by_first_letter')) {
            $this->setData('group_by_first_letter', 0);
        }
        $optionList = $this->getLayout()
            ->createBlock(
                'Swissup\Attributepages\Block\Option\OptionList',
                null,
                ['data' => $this->getData()]
            )
            ->setTemplate($this->getOptionListTemplate());
        if ($this->getRemoveBlockWrapper()) {
            $this->setChild('attribute_view', $optionList);
        } else {
            $attributeBlock = $this->getLayout()
                ->createBlock(
                    'Swissup\Attributepages\Block\Attribute\View',
                    null,
                    ['data' => $this->getData()]
                )
                ->setTemplate($this->getAttributeBlockTemplate());
            $attributeBlock->setChild('children_list', $optionList);
            $this->setChild('attribute_view', $attributeBlock);
        }

        return $this->getChildHtml('attribute_view');
    }
}
