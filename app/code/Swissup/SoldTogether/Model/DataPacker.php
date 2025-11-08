<?php

namespace Swissup\SoldTogether\Model;

use Magento\Framework\Serialize\Serializer\Json;

class DataPacker
{
    /**
     * @var array
     */
    private $map = [
        'w' => 'weight',
        'r' => 'promo_rule',
        'v' => 'promo_value'
    ];

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var array
     */
    private $data;

    /**
     * @param Json  $jsonSerializer
     * @param array $data
     */
    public function __construct(
        Json $jsonSerializer,
        array $data = []
    ) {
        $this->jsonSerializer = $jsonSerializer;
        $this->data = $data;
    }

    public function get($key = null)
    {
        return ($key === null) ?
            $this->data :
            ($this->data[$key]?? null);
    }

    public function set($key, $value = null): self
    {
        if (is_array($key)) {
            $this->data = $key;
        } elseif (is_scalar($key)) {
            $this->data[$key] = $value;
        }

        return $this;
    }

    public function packToJson(): string
    {
        $data = array_map(function ($item) {
            foreach ($this->map as $packedKey => $key) {
                if (isset($item[$key])) {
                    $item[$packedKey] = $item[$key];
                    unset($item[$key]);
                }
            }

            return $item;
        }, $this->data);
        ksort($data);

        return $this->jsonSerializer->serialize($data);
    }

    public function setFromPackedJson(string $json): self
    {
        $data = $this->jsonSerializer->unserialize($json);
        $map = array_flip($this->map);
        $data = array_map(function ($item) use ($map) {
            foreach ($map as $key => $packedKey) {
                if (isset($item[$packedKey])) {
                    $item[$key] = $item[$packedKey];
                    unset($item[$packedKey]);
                }
            }

            return $item;
        }, $data);

        $this->set($data ?: []);

        return $this;
    }
}
