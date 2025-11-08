<?php
namespace Swissup\Askit\Block\Adminhtml\Question\Grid\Renderer;

use Swissup\Askit\Api\Data\MessageInterface;

class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Swissup\Askit\Model\MessageFactory
     */
    protected $modelFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Swissup\Askit\Model\MessageFactory $modelFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Swissup\Askit\Model\MessageFactory $modelFactory,
        array $data = []
    ) {
        $this->modelFactory = $modelFactory;
        parent::__construct($context, $data);
    }

    /**
     * Render statusses
     *
     * @return string
     */
    public function getStatus($row)
    {
        $statuses = $this->modelFactory->create()->getQuestionStatuses();
        if (isset($statuses[$row->getStatus()])) {
            return (string) $statuses[$row->getStatus()];
        }
        return 'None';
    }

    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $class = '';
        $value = $this->getStatus($row);

        switch ($row->getStatus()) {
            case MessageInterface::STATUS_DISAPPROVED:
                $class = 'critical';
                break;
            case MessageInterface::STATUS_APPROVED:
                $class = 'notice';
                break;
            case MessageInterface::STATUS_DISAPPROVED:
                $class = 'minor';
                break;
            case MessageInterface::STATUS_PENDING:
            case MessageInterface::STATUS_CLOSE:
            default:
                $class = 'minor';
                break;
        }
        return '<span class="grid-severity-' . $class . '"><span>' . $value . '</span></span>';
    }
}
