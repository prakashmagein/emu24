<?php
namespace Swissup\Askit\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class MessageActions extends Column
{
    /** Url path */
    const MESSAGE_URL_PATH_EDIT = 'askit/question/edit';
    const QUESTION_URL_PATH_DELETE = 'askit/question/delete';
    const ANSWER___URL_PATH_DELETE = 'askit/answer/delete';
    const MESSAGE_URL_PATH_NEWANSWER = 'askit/answer/new';

    /** @var UrlInterface */
    protected $urlBuilder;

    /**
     * @var string
     */
    private $editUrl;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @param string $editUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $editUrl = self::MESSAGE_URL_PATH_EDIT
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->editUrl = $editUrl;
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
                $name = $this->getData('name');
                if (isset($item['parent_id']) && "0" != $item['parent_id']) {
                    $item[$name]['edit_question'] = [
                        'href' => $this->urlBuilder->getUrl(self::MESSAGE_URL_PATH_EDIT, ['id' => $item['parent_id']]),
                        'label' => __('Edit Question')
                    ];
                }
                if (isset($item['id'])) {
                    if ("0" == $item['parent_id']) {
                        $item[$name]['new_answer'] = [
                            'href' => $this->urlBuilder->getUrl(
                                self::MESSAGE_URL_PATH_NEWANSWER,
                                ['parent_id' => $item['id']]
                            ),
                            'label' => __('New Answer')
                        ];
                    }
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl($this->editUrl, ['id' => $item['id']]),
                        'label' => __('Edit')
                    ];
                    $item[$name]['delete'] = [
                        'href' => (isset($item['parent_id']) && "0" != $item['parent_id']) ?
                            $this->urlBuilder->getUrl(self::ANSWER___URL_PATH_DELETE, ['id' => $item['id'], 'togrid' => true]) :
                            $this->urlBuilder->getUrl(self::QUESTION_URL_PATH_DELETE, ['id' => $item['id']]),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete'),
                            'message' => __('Are you sure you want to do this?')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
