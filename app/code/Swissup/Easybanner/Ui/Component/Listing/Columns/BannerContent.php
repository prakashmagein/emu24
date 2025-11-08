<?php

namespace Swissup\Easybanner\Ui\Component\Listing\Columns;

use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Swissup\Easybanner\Model\Data\Image;

class BannerContent extends \Magento\Ui\Component\Listing\Columns\Column
{
    const NAME = 'banner_content';

    private UrlInterface $urlBuilder;

    private FilterProvider $filterProvider;

    private Image $imageInfo;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        FilterProvider $filterProvider,
        Image $imageInfo,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
        $this->filterProvider = $filterProvider;
        $this->imageInfo = $imageInfo;
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
            $fieldName = $this->getData('name');
            $baseUrl = $this->imageInfo->getBaseUrl();

            foreach ($dataSource['data']['items'] as & $item) {
                $productImgUrl = $baseUrl . '/' . ltrim($item['image'], '/');
                $item[$fieldName . '_alt'] = $item['title'] ?: $item['image'];
                $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                    'easybanner/banner/edit',
                    ['banner_id' => $item['banner_id']]
                );

                if ($item['mode'] === 'html') {
                    $item['html'] = $this->renderHtml($item['html']);

                    preg_match('/\\\\"desktop_image\\\\":\\\\"([^"]*)\\\\"/i', $item['html'], $matches);
                    if (!$matches) {
                        preg_match('/<img[^>]*src=["\']?([^"\']*)/i', $item['html'], $matches);
                    }
                    if ($matches) {
                        $item[$fieldName . '_src'] = $matches[1];
                        $item[$fieldName . '_orig_src'] = $matches[1];
                    }
                } else {
                    $item[$fieldName . '_src'] = $productImgUrl;
                    $item[$fieldName . '_orig_src'] = $productImgUrl;
                }
            }
        }

        return $dataSource;
    }

    private function renderHtml($html)
    {
        $mapping = [
            '{{if' => '{!{if',
            '{{for' => '{!{for',
            '{{var' => '{!{var',
            '{{block' => '{!{block',
            '{{store' => '{!{store',
            '{{depend' => '{!{depend',
            '{{widget' => '{!{widget',
            '{{template' => '{!{template',
        ];

        $html = strtr($html, $mapping);

        try {
            $html = $this->filterProvider->getBlockFilter()->filter($html);
        } catch (\Exception $e) {
            //
        }

        return strtr($html, array_flip($mapping));
    }
}
