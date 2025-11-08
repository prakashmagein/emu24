<?php

namespace Swissup\Askit\Block\Adminhtml\Question\Edit\Tab\Answers\Grid\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;

class Text extends AbstractRenderer
{
    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $text = $this->filterManager->stripTags(
            $this->_getValue($row),
            ['allowableTags' => false, 'escape' => null]
        );
        $text = $this->filterManager->truncate(
            $text,
            ['length' => 120, 'breakWords' => false, 'etc' => '...']
        );

        return $text;
    }
}
