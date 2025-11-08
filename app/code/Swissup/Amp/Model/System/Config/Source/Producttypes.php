<?php
namespace Swissup\Amp\Model\System\Config\Source;

class Producttypes implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    private $productTypes;

    /**
     * @param \Magento\Catalog\Model\Product\Type $productTypes
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Type $productTypes
    ) {
        $this->productTypes = $productTypes;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->productTypes->getOptions();
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $result = [];
        foreach ($this->toOptionArray() as $item) {
            $result[$item['value']] = $item['label'];
        }

        return $result;
    }
}
