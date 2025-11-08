<?php
namespace Swissup\Askit\Block\Adminhtml\Question\Edit\Tab\Answers;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Swissup\Askit\Model\ResourceModel\Answer\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var string[]
     */
    protected $statuses;

    /**
     * @var \Swissup\Askit\Service\GetCurrentQuestionService
     */
    private $currentQuestionService;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Swissup\Askit\Model\ResourceModel\Answer\CollectionFactory $collectionFactory
     * @param \Swissup\Askit\Service\GetCurrentQuestionService $currentQuestionService
     * @param \Swissup\Askit\Model\Message\Source\Status $modelStatus
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Swissup\Askit\Model\ResourceModel\Answer\CollectionFactory $collectionFactory,
        \Swissup\Askit\Service\GetCurrentQuestionService $currentQuestionService,
        \Swissup\Askit\Model\Message\Source\Status $modelStatus,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->collectionFactory = $collectionFactory;
        $this->statuses = $modelStatus->getOptionArray();
        $this->currentQuestionService = $currentQuestionService;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('answersGrid');
        // $this->setDefaultSort('_at');
        $this->setDefaultDir('desc');

        $this->setUseAjax(true);

        $this->setEmptyText(__('No Found'));
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('askit/answer/grid', ['_current' => true]);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $questionId = $this->currentQuestionService->getQuestionId();
        /** @var \Swissup\Askit\Model\ResourceModel\Answer\Collection $collection */
        $collection = $this->collectionFactory->create()
            ->addParentIdFilter($questionId)
            ->addAnswerFilter();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'answer_id', //phantom id
            ['header' => __('ID'), 'align' => 'left', 'index' => 'id', 'width' => 10]
        );

        $this->addColumn(
            'answer_text',
            [
                'header' => __('Text'),
                'type' => 'text',
                'index' => 'text',
                'renderer' => Grid\Renderer\Text::class
                // 'default' => ' ---- '
            ]
        );

        $this->addColumn(
            'answer_customer_name',
            [
                'header' => __('Customer'),
                'type' => 'text',
                'align' => 'center',
                'index' => 'customer_name',
            ]
        );

        $this->addColumn(
            'answer_email',
            [
                'header' => __('Email'),
                'type' => 'text',
                'align' => 'center',
                'index' => 'email',
            ]
        );

        $this->addColumn(
            'answer_hint',
            [
                'header' => __('Votes'),
                'type' => 'text',
                'align' => 'center',
                'index' => 'hint',
            ]
        );

        $this->addColumn(
            'answer_status',
            [
                'header' => __('Status'),
                'align' => 'center',
                'filter' => Grid\Filter\Status::class,
                'index' => 'status',
                'renderer' => Grid\Renderer\Status::class
            ]
        );

        $this->addColumn(
            'answer_update_time',
            [
                'header' => __('Update date'),
                'type' => 'datetime',
                'format' => \IntlDateFormatter::SHORT,
                'align' => 'center',
                'index' => 'update_time',
                'default' => ' ---- '
            ]
        );

        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'align' => 'center',
                'filter' => false,
                'sortable' => false,
                'renderer' => Grid\Renderer\Action::class
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        /** @var \Magento\Backend\Block\Widget\Grid\Massaction\AbstractMassaction $massActionBlock */
        $massActionBlock = $this->getMassactionBlock();
        $massActionBlock->setTemplate('Magento_Catalog::product/grid/massaction_extended.phtml');
        $massActionBlock->setFormFieldName('selected');

        // $this->getMassactionBlock()->setUseAjax(true);
        $massActionBlock->setHideFormElement(true);

        $massActionBlock->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('askit/message/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->statuses;

        array_unshift($statuses, ['label' => '', 'value' => '']);
        $massActionBlock->addItem(
            'change_status',
            [
                'label' => __('Change Status'),
                'url' => $this->getUrl('askit/message/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'change_status',
                        'type' => 'select',
                        // 'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => $statuses
                    ]
                ]
            ]
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('askit/answer/edit', ['id' => $row->getId()]);
    }

    /**
     * {@inheritdoc}
     *
     * Fixed eval error "unexpected token 'case'"
     */
    public function getMassactionBlockHtml()
    {
        $html =  parent::getMassactionBlockHtml();
        $html = str_replace('    break', '    break;', $html);

        return $html;
    }
}
