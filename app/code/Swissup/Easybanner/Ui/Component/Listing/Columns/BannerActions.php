<?php

namespace Swissup\Easybanner\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class BannerActions extends Column
{
    /** Url path */
    const URL_PATH_EDIT = 'easybanner/banner/edit';
    const URL_PATH_DELETE = 'easybanner/banner/delete';
    const URL_PATH_ENABLE = 'easybanner/banner/enable';
    const URL_PATH_DISABLE = 'easybanner/banner/disable';

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
                if (isset($item['banner_id'])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'banner_id' => $item['banner_id']
                                ]
                            ),
                            'label' => __('Edit')
                        ],
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'banner_id' => $item['banner_id']
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
                                    'banner_id' => $item['banner_id']
                                ]
                            ),
                            'label' => __('Enable')
                        ],
                        'disable' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DISABLE,
                                [
                                    'banner_id' => $item['banner_id']
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
