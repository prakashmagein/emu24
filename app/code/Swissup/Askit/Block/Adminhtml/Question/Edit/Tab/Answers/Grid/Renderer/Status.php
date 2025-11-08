<?php

namespace Swissup\Askit\Block\Adminhtml\Question\Edit\Tab\Answers\Grid\Renderer;

use Swissup\Askit\Api\Data\MessageInterface;
use Swissup\Askit\Model\Message;

class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var array
     */
    private $statuses;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Swissup\Askit\Model\Message\Source\Status $modelStatus
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Swissup\Askit\Model\Message\Source\Status $modelStatus,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->statuses = $modelStatus->getOptionArray();
    }

    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $class = '';
        $value = __($this->getStatus($row->getStatus()));

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
        return '<span class="grid-severity-' . $class . '">' .
            '<span>' . $value . '</span>' .
        '</span>';
    }

    /**
     * @param string $status
     * @return \Magento\Framework\Phrase
     */
    public function getStatus($status)
    {
        if (isset($this->statuses[$status])) {
            return $this->statuses[$status];
        }

        return __('Unknown');
    }
}
