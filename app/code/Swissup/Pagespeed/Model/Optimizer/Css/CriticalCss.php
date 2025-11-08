<?php
namespace Swissup\Pagespeed\Model\Optimizer\Css;

use Swissup\Pagespeed\Helper\Config;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Swissup\Pagespeed\Model\Optimizer\AbstractOptimizer;

class CriticalCss extends AbstractOptimizer
{
    /**
     *
     * @var \Swissup\Pagespeed\Model\Css\Improver
     */
    private $cssImprover;

    /**
     * @param Config $config
     * @param \Swissup\Pagespeed\Model\Css\Improver $cssImprover
     */
    public function __construct(
        Config $config,
        \Swissup\Pagespeed\Model\Css\Improver $cssImprover
    ) {
        parent::__construct($config);
        $this->cssImprover = $cssImprover;
    }

    /**
     * Perform result postprocessing
     *
     * @param ResponseHttp $response
     * @return ResponseHttp
     */
    public function process(?ResponseHttp $response = null)
    {
        if (!$response || !$this->config->isCriticalCssEnable()) {
            return $response;
        }

        $html = (string) $response->getBody();
        if (empty($html) || strpos($html, '<html') === false) {
            return $response;
        }

        $startSignature = '<style type="text/css" data-type="criticalCss">';
        $startPosition = strpos($html, $startSignature);
        $startPosition += strlen($startSignature);

        $endPosition = strpos($html, '<', $startPosition);

        $styles = substr(
            $html,
            $startPosition,
            $endPosition - $startPosition
        );

        if (empty($styles)) {
            return $response;
        }

        $improvedStyles = $this->cssImprover
            ->setResponse($response)
            ->process($styles);

        $html = str_replace($styles, $improvedStyles, $html);
        $response->setBody($html);

        return $response;
    }
}
