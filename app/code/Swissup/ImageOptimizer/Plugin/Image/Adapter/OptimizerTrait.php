<?php
namespace Swissup\ImageOptimizer\Plugin\Image\Adapter;

use Swissup\ImageOptimizer\Helper\Config;
use Spatie\ImageOptimizer\OptimizerChain;
use Swissup\ImageOptimizer\Model\Image\Optimizers\OptimizerChainFactory;

trait OptimizerTrait
{
    /**
     * @var \Swissup\ImageOptimizer\Helper\Config
     */
    private $configHelper;

    /**
     *
     * @param \Swissup\ImageOptimizer\Helper\Config $configHelper
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(\Swissup\ImageOptimizer\Helper\Config $configHelper)
    {
        $this->configHelper = $configHelper;
    }

    /**
     *
     * @var \Spatie\ImageOptimizer\OptimizerChain|null
     */
    private $optimizerChain;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     *
     * @param  string $filename
     * @return void
     */
    public function optimize($filename)
    {
        if ($this->configHelper->isImageOptimizerEnable()) {
            try {
                $this->getOptimizerChain()->optimize($filename);
            } catch (\Exception $e) {
                /** @var \Psr\Log\LoggerInterface|boolean $logger */
                $logger = $this->getLogger();
                if ($logger) {
                    $logger->critical($e->getMessage());
                }
            }
        }
    }

    /**
     * @return \Psr\Log\LoggerInterface|false
     */
    private function getLogger()
    {
        if ($this->configHelper->useLoggingUntilImageOptimise() === false) {
            return false;
        }
        $logger = false;
        /** @var \Psr\Log\LoggerInterface|null $logger */
        if (isset($this->logger)) {
            $logger = $this->logger;
        }

        return $logger;
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @return $this
     */
    public function setLogger(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     *
     * @return \Spatie\ImageOptimizer\OptimizerChain
     */
    private function getOptimizerChain()
    {
        if (!$this->optimizerChain) {

            $optimizersConfig = [];
            if ($this->configHelper->isWebPEnable()) {
                $optimizersConfig['convert_to_webp'] = true;
            }
            if ($this->configHelper->isImageOptimizersRemote()) {
                $optimizersConfig['remote'] = true;
                $optimizersConfig['apiUrl'] = $this->configHelper->getImageOptimizeServiceAPIUrl();
                $optimizersConfig['apiKey'] = $this->configHelper->getImageOptimizeServiceAPIKey();
                $optimizersConfig['baseUrl'] = $this->configHelper->getBaseUrl();
                $optimizersConfig['mediaDir'] = $this->configHelper->getMediaDir();
            }
            /** @var \Spatie\ImageOptimizer\OptimizerChain $optimizerChain */
            $optimizerChain = OptimizerChainFactory::create($optimizersConfig);/** @phpstan-ignore-line */
            $this->optimizerChain = $optimizerChain;

            $timeout = $this->configHelper->getImageOptimizerTimeout();
            $optimizerChain->setTimeout($timeout);

            /** @var \Psr\Log\LoggerInterface|boolean $logger */
            $logger = $this->getLogger();
            if ($logger) {
                $optimizerChain->useLogger($logger);
            }

            $this->optimizerChain = $optimizerChain;
        }
        return $this->optimizerChain;
    }
}
