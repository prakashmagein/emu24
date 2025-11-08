<?php
namespace Swissup\Askit\Controller\Adminhtml\Answer;

use Swissup\Askit\Controller\Adminhtml\Message\AbstractGrid as MessageGrid;
use Magento\Backend\App\Action\Context;

class Grid extends MessageGrid
{
    /**
     * @var string
     */
    protected $gridBlockName = 'askit_answer_listing';

    /**
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id', false);
        if ($id) {
            $this->_getSession()->setData('askit_question_id', $id);
        }

        return parent::execute();
    }
}
