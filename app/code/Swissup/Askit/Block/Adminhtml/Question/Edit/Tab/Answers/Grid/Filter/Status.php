<?php
namespace Swissup\Askit\Block\Adminhtml\Question\Edit\Tab\Answers\Grid\Filter;


class Status extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    /**
     * @var array
     */
    protected $statuses;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param \Swissup\Askit\Model\Message\Source\Status $modelStatus
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Swissup\Askit\Model\Message\Source\Status $modelStatus,
        array $data = []
    ) {
        parent::__construct($context, $resourceHelper, $data);
        $this->statuses = $modelStatus->getOptionArray();
    }

    /**
     * @return array
     */
    protected function _getOptions()
    {
        $options = [
            ['value' => '', 'label' => '']
        ];
        foreach ($this->statuses as $status => $label) {
            $options[] = ['value' => $status, 'label' => __($label)];
        }

        return $options;
    }

    /**
     * @return array|null
     */
    public function getCondition()
    {
        return $this->getValue() === null ? null : ['eq' => $this->getValue()];
    }
}
