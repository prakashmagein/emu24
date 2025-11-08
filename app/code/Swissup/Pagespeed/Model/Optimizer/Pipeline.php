<?php
namespace Swissup\Pagespeed\Model\Optimizer;

use Magento\Framework\App\Response\Http;

class Pipeline
{
    public function run(Http $response, array $optimizers): Http
    {
        foreach ($optimizers as $optimizer) {
            $response = $optimizer->process($response);
        }

        return $response;
    }
}

