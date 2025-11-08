<?php

namespace Swissup\Attributepages\Block\Adminhtml\Page\Edit\Tab\Renderer;

use Magento\Framework\DataObject;

class SortOrder extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(DataObject $row)
    {
        return '<input type="text" class="input-text"
            value="' . ($row->getSortOrder() ?? 100) . '"
            name="option['. $row->getOptionId() .'][sort_order]"
            style="width: 80px;"
        />';
    }
}
