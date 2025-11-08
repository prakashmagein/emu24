<?php
namespace Swissup\Askit\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

use Swissup\Askit\Api\Data\MessageInterface;

class QuestionText extends \Magento\Ui\Component\Listing\Columns\Column
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
        $this->uiComponentFactory = $uiComponentFactory;
        $this->modelFactory = $modelFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
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
                $item[$this->getData('name')] = $this->getQuestionText($item);
            }
        }
        return $dataSource;
    }

    /**
     * Render question text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getQuestionText(array $item)
    {
        $questionId = $item['parent_id'];
        $question = $this->modelFactory->create()->load($questionId);
        return $question->getText();
    }
}
