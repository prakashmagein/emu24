<?php
namespace Swissup\Pagespeed\Model\Optimizer;

use Magento\Framework\Controller\ResultInterface;

class Selector
{
    public function getApplicable(ResultInterface $result, array $optimizers): array
    {
        return array_filter($optimizers, fn(OptimizerInterface $o) => $o->isApplicable($result));
    }
}
