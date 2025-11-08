<?php

namespace Swissup\SoldTogether\Api;

interface LinkedItemsProcessor
{
    /**
     * Process linked items (products). Preprocess data before output.
     *
     * @param  array  $items
     */
    public function process(array $items): void;
}
