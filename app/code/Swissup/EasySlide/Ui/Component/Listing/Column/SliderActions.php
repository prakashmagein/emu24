<?php

namespace Swissup\EasySlide\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class SliderActions extends Column
{
    /** Url path */
    const URL_PATH_EDIT = 'easyslide/slider/edit';
    const URL_PATH_DUPLICATE = 'easyslide/slider/duplicate';
    const URL_PATH_DELETE = 'easyslide/slider/delete';
    const URL_PATH_ENABLE = 'easyslide/slider/enable';
    const URL_PATH_DISABLE = 'easyslide/slider/disable';

    /** @var UrlInterface */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
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
                if (isset($item['slider_id'])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'slider_id' => $item['slider_id']
                                ]
                            ),
                            'label' => __('Edit')
                        ],
                        'duplicate' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DUPLICATE,
                                [
                                    'slider_id' => $item['slider_id']
                                ]
                            ),
                            'label' => __('Duplicate')
                        ],
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'slider_id' => $item['slider_id']
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete'),
                                'message' => __('Are you sure you want to do this?')
                            ]
                        ],
                        'enable' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_ENABLE,
                                [
                                    'slider_id' => $item['slider_id']
                                ]
                            ),
                            'label' => __('Enable')
                        ],
                        'disable' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DISABLE,
                                [
                                    'slider_id' => $item['slider_id']
                                ]
                            ),
                            'label' => __('Disable')
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}
