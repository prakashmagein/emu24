<?php

namespace Swissup\Highlight\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Remove all forbidden data parameters
     *
     * @param  array $data
     * @return array
     */
    public function filterBlockData(array $data)
    {
        $filtered = array_filter(
            $data,
            [$this, 'isAllowedBlockData'],
            ARRAY_FILTER_USE_KEY
        );

        if (isset($filtered['type'])) {
            if (strpos($filtered['type'], 'Swissup\Highlight') !== 0) {
                unset($filtered['type']);
            } else {
                $filtered['type'] = str_replace('\Interceptor', '', $filtered['type']);
            }
        }

        return $filtered;
    }

    /**
     * Check if data parameter is allowed
     *
     * @param  string  $key
     * @return boolean      [description]
     */
    public function isAllowedBlockData($key)
    {
        $whitelist = array(
            'attribute_code',
            'carousel',
            'column_count',
            'conditions_encoded',
            'category_ids',
            'dir',
            'min_popularity',
            'mode',
            'order',
            'page_count',
            'page_var_name',
            'period',
            'products_count',
            'template',
            'type'
        );
        return in_array($key, $whitelist);
    }


    /**
     * Get data to initialize Swiper
     *
     * @param  \Swissup\Highlight\Block\ProductList\All $block
     * @param  string $dataSourceUrl [description]
     * @param  string $format        [description]
     * @return string|array
     */
    public function getSwiperData(
        $block,
        $dataSourceUrl = '',
        $format = 'json'
    ) {
        $blockData = $block->getData();
        // compatibility with `Content > Widgets` created widgets
        if (empty($blockData['template'])) {
            $blockData['template'] = $block->getTemplate();
        }

        // minimize params count
        if (isset($blockData['conditions_encoded'])) {
            $conditions = $block->getConditionsDecoded();
            if (!count($conditions) || count($conditions) === 1) {
                unset($blockData['conditions_encoded']);
            }
        }

        $swiperParams = [
            'slidesPerView' => 1,
            'navigation' => [
                'nextEl' => '.swiper-button-next',
                'prevEl' => '.swiper-button-prev'
            ],
            'dataSourceUrl' => $dataSourceUrl ? $dataSourceUrl : $this->_getUrl('highlight/carousel/slide'),
            'blockData' => $this->filterBlockData($blockData)
        ];

        // disable carousel on small screens
        $swiperParams['destroy'] = [
            // @see highliht.less
            'media' => '(max-width: 640px)'
        ];

        if ($format == 'json') {
            return json_encode($swiperParams, JSON_HEX_APOS);
        }

        return $swiperParams;
    }
}
