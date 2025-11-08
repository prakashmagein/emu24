<?php

namespace Swissup\Askit\Block\Question;

use Magento\Widget\Block\BlockInterface;

class Widget extends \Magento\Framework\View\Element\Template implements BlockInterface
{
    /**
     * Default template to use for listing question widget
     */
    const DEFAULT_LIST_TEMPLATE = 'question/listing-ajax.phtml';

    /**
     * Default template to use for new question form widget
     */
    const DEFAULT_FORM_TEMPLATE = 'question/form.phtml';

    protected function _toHtml()
    {
        $html = '';
        $isShowForm = !$this->getIsHideBlockForm();
        if ($isShowForm) {
            /** @var \Magento\Framework\View\Element\AbstractBlock $formBlock */
            $formBlock = $this->getLayout()->createBlock(\Swissup\Askit\Block\Question\Form::class);
            $formBlock->setTemplate(self::DEFAULT_FORM_TEMPLATE);
            $html .= $formBlock->toHtml();
        }

        $isShowListing = !$this->getIsHideBlockListing();
        if ($isShowListing) {
            /** @var \Magento\Framework\View\Element\AbstractBlock $listingBlock */
            $listingBlock = $this->getLayout()->createBlock(\Swissup\Askit\Block\Question\Listing::class);
            $listingBlock->setTemplate(self::DEFAULT_LIST_TEMPLATE);
            $html .= $listingBlock->toHtml();

            $title = $listingBlock->getTitle();
            $this->setTitle($title);
            $this->setTabTitle($title);
            $this->setCount($listingBlock->getCollectionSize());
        }

        return $html;
    }
}
