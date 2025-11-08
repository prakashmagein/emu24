<?php
namespace Swissup\Askit\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

use Swissup\Askit\Api\Data\MessageInterface;

class Status extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \Swissup\Askit\Model\MessageFactory
     */
    protected $modelFactory;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Swissup\Askit\Model\MessageFactory $modelFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->uiComponentFactory = $uiComponentFactory;
        $this->modelFactory = $modelFactory;
    }

    /**
     * Render statusses
     *
     * @return string|\Magento\Framework\Phrase
     */
    public function getStatus($status)
    {
        $statuses = $this->modelFactory->create()->getQuestionStatuses();
        if (isset($statuses[$status])) {
            return $statuses[$status];
        }

        return 'None';
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $this->prepareItem($item);
            }
        }
        return $dataSource;
    }

    protected function prepareItem(array $item)
    {
        $value = $this->getStatus($item['status']);
        switch ($item['status']) {
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
