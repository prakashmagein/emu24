<?php

namespace Swissup\Easybanner\Block\Adminhtml\Widget;

abstract class AbstractChooser extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var string
     */
    protected $_routePath;

    /**
     * @var object (model factory)
     */
    protected $_entityFactory;

    /**
     * @var object (collection factory)
     */
    protected $_collectionFactory;

    /**
     * @var string
     */
    protected $_entityLabelField;

    /**
     * Block construction, prepare grid params
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        //$this->setDefaultSort('name');
        $this->setUseAjax(true);
        $this->setDefaultFilter(['chooser_status' => '1']);
    }

    /**
     * Prepare chooser element HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element Form Element
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function prepareElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $uniqId = $this->mathRandom->getUniqueHash($element->getId());
        $sourceUrl = $this->getUrl($this->_routePath, ['uniq_id' => $uniqId]);

        $chooser = $this->getLayout()->createBlock(
            'Magento\Widget\Block\Adminhtml\Widget\Chooser'
        )->setElement(
            $element
        )->setConfig(
            $this->getConfig()
        )->setFieldsetId(
            $this->getFieldsetId()
        )->setSourceUrl(
            $sourceUrl
        )->setUniqId(
            $uniqId
        )->setLabel(
            $this->getLabelForValue($element->getValue())
        );
        $element->setData('after_element_html', $chooser->toHtml());
        return $element;
    }

    /**
     * Prepare enities collection using collection factory
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Grid Row JS Callback
     *
     * @return string
     */
    public function getRowClickCallback()
    {
        $chooserJsObject = $this->getId();
        $js = '
            function (grid, event) {
                var trElement = Event.findElement(event, "tr");
                var pageTitle = trElement.down("td").next().innerHTML;
                var pageId = trElement.down("td").innerHTML.replace(/^\s+|\s+$/g,"");
                ' .
            $chooserJsObject .
            '.setElementValue(pageId);
                ' .
            $chooserJsObject .
            '.setElementLabel(pageTitle);
                ' .
            $chooserJsObject .
            '.close();
            }
        ';
        return $js;
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl($this->_routePath, ['_current' => true]);
    }

    /**
     * Get label for value from chooser
     *
     * @param  mixed $value [description]
     * @return string
     */
    public function getLabelForValue($value)
    {
        $label = '';
        if (isset($value)) {
            $entity = $this->_entityFactory->create()->load((int)$value);
            if ($entity->getId()) {
                $label = $entity->getData($this->_entityLabelField);
            }
        }

        return $this->escapeHtml($label);
    }
}
