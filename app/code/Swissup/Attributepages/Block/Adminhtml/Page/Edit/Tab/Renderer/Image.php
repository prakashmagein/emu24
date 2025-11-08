<?php
namespace Swissup\Attributepages\Block\Adminhtml\Page\Edit\Tab\Renderer;

use \Magento\Framework\DataObject;
use \Magento\Framework\UrlInterface;
use Swissup\Attributepages\Model\ImageData;

class Image extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_type = 'image';

    public function render(DataObject $row)
    {
        $output = '<div style="float: left; width: 25px; height: 25px;">';
        if ($image = $row->getData($this->_type)) {
            $output .= '<img src="' . $this->getBaseUrl()
                . ImageData::ENTITY_MEDIA_PATH
                . '/'
                . trim($image, '/')
                . '"'
                . 'alt="' . $row->getIdentifier() . '" width="25" height="25"/>';
        }
        $output .= '</div>';

        $optionId = $row->getOptionId();
        $fieldName = 'option_' . $optionId . '_' . $this->_type;
        $output .= '<input type="file" style="width: 180px;" name="' . $fieldName . '"/>';
        $output .= '<input type="hidden" value="' . $row->getEntityId() . '" name="option['. $optionId .'][entity_id]"/>';

        if ($row->getData($this->_type)) {
            $output .= '<div class="delete-image">';
            $output .= '<input type="checkbox" value="1" name="option['. $optionId .'][' . $fieldName . '][delete]" id="' . $fieldName . '_delete"/>';
            $output .= '<label onclick="event.stopPropagation()" for="' . $fieldName . '_delete">' . __('Delete') . '</label>';
            $output .= '</div>';
        }
        return $output;
    }
    /**
     * Get images base url
     *
     * @return string
     */
    public function getBaseUrl($type = UrlInterface::URL_TYPE_MEDIA)
    {
        return $this->_urlBuilder->getBaseUrl(['_type' => $type]);
    }
}
