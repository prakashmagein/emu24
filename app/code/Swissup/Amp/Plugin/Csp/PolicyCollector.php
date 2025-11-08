<?php

namespace Swissup\Amp\Plugin\Csp;

use Magento\Csp\Model\Collector\DynamicCollector;
use Magento\Csp\Model\Policy\FetchPolicy;

class PolicyCollector
{
    /**
     * @var \Swissup\Amp\Helper\Data
     */
    private $helper;

    /**
     * @param \Swissup\Amp\Helper\Data $helper
     */
    public function __construct(
        \Swissup\Amp\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param DynamicCollector $subject
     * @param array $result
     * @return array
     */
    public function afterCollect(
        DynamicCollector $subject,
        array $result = []
    ) {
        if (!$this->helper->canUseAmp()) {
            return $result;
        }

        $result[] = new FetchPolicy('worker-src', false, [], ['blob']);

        return $result;
    }
}
