<?php

namespace Swissup\ThemeEditorArgentobreezeBusiness\Model\Config\Backend\Category;

class MoveDescription extends \Swissup\ThemeEditor\Model\Config\Backend\Category\MoveDescription
{
    protected function _construct()
    {
        parent::_construct();

        // move category description before all columns
        $this->mapping['main.content.before'] = '<move element="category.description" destination="columns.top" />';
    }
}
