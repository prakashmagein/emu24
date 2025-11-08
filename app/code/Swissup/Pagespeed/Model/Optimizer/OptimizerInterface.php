<?php
namespace Swissup\Pagespeed\Model\Optimizer;

use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\Controller\ResultInterface;

interface OptimizerInterface
{
    /**
     * Perform result postprocessing
     *
     * @param ResponseHttp $response
     * @return ResponseHttp
     */
    public function process(?ResponseHttp $response = null);

    /**
     * Check if the optimizer is applicable for the given result
     *
     * @param ResultInterface $result
     * @return bool
     */
    public function isApplicable(ResultInterface $result): bool;
}
