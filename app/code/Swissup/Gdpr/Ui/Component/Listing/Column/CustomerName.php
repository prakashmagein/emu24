<?php

namespace Swissup\Gdpr\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\Escaper;

class CustomerName extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param Escaper $escaper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper;
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
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            if (empty($item[$this->getData('name')])) {
                $item[$this->getData('name')] = __('Guest');
            } else {
                $item[$this->getData('name')] = sprintf(
                    '<a href="%s" title="%s">%s</a>',
                    $this->urlBuilder->getUrl(
                        'customer/index/edit',
                        [
                            'id' => $item['customer_id'],
                        ]
                    ),
                    $this->escaper->escapeHtml(__("Open Customer Page")),
                    $this->escaper->escapeHtml($item[$this->getData('name')])
                );
            }
        }

        return $dataSource;
    }
}
