<?php

namespace Swissup\Easybanner\Block\Adminhtml\Banner\Edit\Tab;

use Magento\Framework\View\Element\Template\Context;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Statistics extends \Magento\Framework\View\Element\Template implements TabInterface
{
    private $_authorization;
    /**
     * @var string
     */
    protected $_template = 'statistics.phtml';

    private $registry;

    /**
     * @var string
     */
    protected $_nameInLayout = 'statistics_content';

    /**
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->registry = $registry;
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Statistics');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Statistics');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        $model = $this->registry->registry('easybanner_banner');

        return $model->getId() && $model->getIsTrackable();
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
