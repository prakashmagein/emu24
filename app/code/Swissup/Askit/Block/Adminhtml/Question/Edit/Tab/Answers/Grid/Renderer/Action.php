<?php
namespace Swissup\Askit\Block\Adminhtml\Question\Edit\Tab\Answers\Grid\Renderer;

/**
 * Adminhtml newsletter queue grid block action item renderer
 */
class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $actions = [];
        $actions[] = [
            '@' => [
                'href' => $this->getUrl('askit/answer/edit', ['id' => $row->getId()]),
                'target' => '_blank',
                'title' => __('Edit'),
                'style' => 'font-family: \'Admin Icons\'; font-size: 1.2em; color: #514943; text-decoration: none',
            ],
            '#' => '&#xe631;',
        ];

        $actions[] = [
            '@' => [
                'href' => $this->getUrl('askit/answer/delete', ['id' => $row->getId()]),
                'onClick' => sprintf("deleteConfirm('%s', this.href); return false;", __('Delete answer. Are you sure?')),
                'title' => __('Delete'),
                'style' => 'font-family: \'Admin Icons\'; font-size: 1.2em; color: #514943; text-decoration: none',
            ],
            '#' => '&#xe630;',
        ];

        return $this->_actionsToHtml($actions);
    }

    /**
     * @param string $value
     * @return string
     */
    protected function _getEscapedValue($value)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return addcslashes($this->escapeHtml($value), '\\\'');
    }

    /**
     * @param array $actions
     * @return string
     */
    protected function _actionsToHtml(array $actions)
    {
        $html = [];
        $attributesObject = new \Magento\Framework\DataObject();
        foreach ($actions as $action) {
            $attributesObject->setData($action['@']);
            $html[] = '<a ' . $attributesObject->serialize() . '>' . $action['#'] . '</a>';
        }

        return implode('<span class="separator">&nbsp;</span>', $html);
    }
}
