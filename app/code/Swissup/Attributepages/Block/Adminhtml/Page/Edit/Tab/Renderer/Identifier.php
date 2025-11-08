<?php
namespace Swissup\Attributepages\Block\Adminhtml\Page\Edit\Tab\Renderer;

use Magento\Framework\DataObject;

class Identifier extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(DataObject $row)
    {
        return '<input type="text" class="input-text required-entry"
            value="' . $row->getIdentifier() . '"
            name="option['. $row->getOptionId() .'][identifier]"
        />';
    }
}
