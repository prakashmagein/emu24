<?php
namespace Swissup\Pagespeed\Plugin\Controller\Result;

use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\View\Result\Layout;
use Swissup\Pagespeed\Helper\Config;
use Swissup\Pagespeed\Model\Optimizer\Coordinator;

/**
 * Plugin for processing HTML response optimization
 */
class AfterRenderResultPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Coordinator
     */
    private $coordinator;

    /**
     * @param Config $config
     * @param Coordinator $coordinator
     */
    public function __construct(
        Config $config,
        Coordinator $coordinator
    ) {
        $this->config = $config;
        $this->coordinator = $coordinator;
    }

    /**
     * Perform result postprocessing
     *
     * @param ResultInterface $subject
     * @param ResultInterface $result
     * @param ResponseHttp $response
     * @return ResultInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRenderResult(
        ResultInterface $subject,
        ResultInterface $result,
        ResponseHttp $response
    ): ResultInterface {
        if (!$this->shouldProcessResponse($response, $result)) {
            return $result;
        }
        $this->coordinator->run($result, $response);

        return $result;
    }

    /**
     * Check if response should be processed
     *
     * @param ResponseHttp|null $response
     * @param ResultInterface $result
     * @return bool
     */
    private function shouldProcessResponse($response, ResultInterface $result): bool
    {
        if ($response === null) {
            return false;
        }

        if (!$result instanceof Layout) {
            return false;
        }

        if (!$this->config->isEnabled()) {
            return false;
        }

        if (!$this->config->isEnableDynamicHtmlProcessing()) {
            return false;
        }

        return true;
    }
}
