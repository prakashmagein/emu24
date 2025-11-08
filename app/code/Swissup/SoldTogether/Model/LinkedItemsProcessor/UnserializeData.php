<?php

namespace Swissup\SoldTogether\Model\LinkedItemsProcessor;

use Magento\Framework\Serialize\Serializer\Json;

class UnserializeData
{
    /**
     * @var Json
     */
    private $json;

    /**
     * @param Json $json
     */
    public function __construct(
        Json $json
    ) {
        $this->json = $json;
    }

    public function process(array $items)
    {
        foreach ($items as $item) {
            $product = $item['model'];
            $serialized = (string)$product->getData('data_serialized');
            $product->unsetData('data_serialized');
            $data = $serialized ? $this->json->unserialize($serialized) : [];
            $product->setData('soldtogether_data', $data ?: []);
        }
    }
}
