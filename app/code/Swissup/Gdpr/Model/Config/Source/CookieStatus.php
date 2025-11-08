<?php

namespace Swissup\Gdpr\Model\Config\Source;

class CookieStatus implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Swissup\Gdpr\Model\Cookie
     */
    private $cookie;

    /**
     * @param \Swissup\Gdpr\Model\Cookie $cookie
     */
    public function __construct(\Swissup\Gdpr\Model\Cookie $cookie)
    {
        $this->cookie = $cookie;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->cookie->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
