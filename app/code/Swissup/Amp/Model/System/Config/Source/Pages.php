<?php
namespace Swissup\Amp\Model\System\Config\Source;

class Pages implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->eventManager = $eventManager;
    }

    /**
     * Get supported pages
     *
     * @return array
     */
    public function toOptionArray()
    {
        $object = new \Magento\Framework\DataObject([
            'cms_index_index'            => __('Homepage'),
            'catalog_product_view'       => __('Product Page'),
            'catalog_category_view'      => __('Category Page'),
            'catalogsearch_result_index' => __('Catalog Search'),
            'cms_page_view'              => __('Cms Pages'),
            'contact_index_index'        => __('Contact Us Page'),
        ]);

        $this->eventManager->dispatch(
            'swissupamp_prepare_pages_config',
            ['pages' => $object]
        );

        $result = [];
        foreach ($object->getData() as $value => $label) {
            $result[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $result;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $result = [];
        foreach ($this->toOptionArray() as $option) {
            $result[$option['value']] = $option['label'];
        }

        return $result;
    }
}
