<?php
namespace Swissup\Askit\Controller\Adminhtml\Assign;

abstract class AbstractAction extends \Magento\Backend\App\Action
{
    /**
     * @var string
     */
    protected $listingBlockName = '';

    /**
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $this->_getSession()->setData('askit_question_id', $id);
        }

        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(
            \Magento\Framework\Controller\ResultFactory::TYPE_LAYOUT
        );
        /** @var \Magento\Framework\View\Element\AbstractBlock $grid */
        $grid = $resultLayout->getLayout()->getBlock($this->listingBlockName);
        $grid->setUseAjax(true);

        return $resultLayout;
    }
}
