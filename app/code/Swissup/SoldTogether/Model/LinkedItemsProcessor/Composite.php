<?php

namespace Swissup\SoldTogether\Model\LinkedItemsProcessor;

class Composite implements \Swissup\SoldTogether\Api\LinkedItemsProcessor
{
    /**
     * @var array
     */
    private $processors;

    /**
     * @param array $processors
     */
    public function __construct(
        array $processors = []
    ) {
        $this->processors = $processors;
        ksort($this->processors);
    }

    public function process(array $items): void
    {
        foreach ($this->processors as $processor) {
            $processor->process($items);
        }
    }
}
