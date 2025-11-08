<?php

namespace Swissup\Navigationpro\Model\Template;

/**
 * Item Name Renderer. Examples
 *
 * Get item url:
 *     {{navpro data="url"}}
 *
 * Get Remote Entity name:
 *     {{navpro data="remote_entity.name"}}
 *
 * Call remote entity method:
 *     {{navpro data="remote_entity.getProductCount"}}
 */
class Filter extends \Magento\Widget\Model\Template\Filter
{
    protected $item;

    /**
     * @param \Magento\Framework\Data\Tree\Node $item
     */
    public function setItem(\Magento\Framework\Data\Tree\Node $item)
    {
        $this->item = $item;
        return $this;
    }

    /**
     * @return \Magento\Framework\Data\Tree\Node $item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param  array $construction
     * @return string
     */
    public function navproDirective($construction)
    {
        $item   = $this->getItem();
        $params = $this->getParameters($construction[2]);
        if (!isset($params['data']) || !$item) {
            return '';
        }

        $data = $item->getData();
        foreach (explode('.', $params['data']) as $segment) {
            if (is_object($data)) {
                if (strpos($segment, 'get') === 0) {
                    $data = $data->{$segment}();
                    continue;
                } else {
                    // short notation support: remote_entity.name
                    $data = $data->getData();
                }
            }

            if (is_array($data) && array_key_exists($segment, $data)) {
                $data = $data[$segment];
            } else {
                return '';
            }
        }

        return $data;
    }

    /**
     * @param string $value
     * @return string|string[]
     */
    public function filter($value)
    {
        $identifier = $this->getItem()->getTree()->getIdentifier();

        $fingerprints = [
            '"Swissup\Navigationpro\Block\Widget\Menu" identifier="' . $identifier . '"',
            '"Swissup\Navigationpro\Block\Menu" identifier="' . $identifier . '"'
        ];

        foreach ($fingerprints as $fingerprint) {
            if (false !== strstr($value, $fingerprint)) {
                return 'Error: Recursion Call Detected';
            }
        }

        $value = parent::filter($value);

        // Magento 2.4 fix
        $value = str_replace(
            [
                '/pub/media//catalog/category//pub/media/catalog/category/',
                '/media//catalog/category//pub/media/catalog/category/',
                '/media//catalog/category//media/catalog/category/',
            ],
            [
                '/pub/media/catalog/category/',
                '/pub/media/catalog/category/',
                '/media/catalog/category/',
            ],
            $value
        );

        // Remove cached uenc
        $value = preg_replace('/"uenc":".*?"/im', '"uenc":""', $value);

        return $value;
    }
}
