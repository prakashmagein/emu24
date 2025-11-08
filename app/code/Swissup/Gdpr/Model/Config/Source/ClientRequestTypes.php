<?php

namespace Swissup\Gdpr\Model\Config\Source;

class ClientRequestTypes implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Swissup\Gdpr\Model\ClientRequest
     */
    private $clientRequest;

    /**
     * @param \Swissup\Gdpr\Model\ClientRequest $clientRequest
     */
    public function __construct(
        \Swissup\Gdpr\Model\ClientRequest $clientRequest
    ) {
        $this->clientRequest = $clientRequest;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->clientRequest->getAvailableRequestTypes() as $value => $label) {
            $result[] = [
                'value' => $value,
                'label' => $label,
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
        return $this->clientRequest->getAvailableRequestTypes();
    }
}
