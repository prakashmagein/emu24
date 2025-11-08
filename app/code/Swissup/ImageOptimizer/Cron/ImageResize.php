<?php

namespace Swissup\ImageOptimizer\Cron;

class ImageResize
{
    const MAX_LIMIT = 10000;

    /**
     *
     * @var \Swissup\ImageOptimizer\Model\ImageResize
     */
    private $service;

    /**
     *
     * @var \Swissup\ImageOptimizer\Helper\Config
     */
    private $configHelper;

    /**
     * @param \Swissup\ImageOptimizer\Model\ImageResize $service
     * @param \Swissup\ImageOptimizer\Helper\Config $configHelper
     */
    public function __construct(
        \Swissup\ImageOptimizer\Model\ImageResize $service,
        \Swissup\ImageOptimizer\Helper\Config $configHelper
    ) {
        $this->service = $service;
        $this->configHelper = $configHelper;
    }

    /**
     * Run the warm cache process.
     * @return $this
     */
    public function execute()
    {
        if ($this->configHelper->isCronEnabled()) {

            $limit = $this->getLimit();

            $this->service->setLimit($limit);
            $generators = [
                $this->service->resizeCustomImages(),
                $this->service->resizeAllProductImages()
            ];

            foreach ($generators as $label => $generator) {
                $generator->current();
                for (; $generator->valid(); $generator->next()) {
                    $generator->key();
                }
            }
        }

        return $this;
    }

    /**
     *
     * @return int
     */
    private function getLimit()
    {
        $limit = $this->configHelper->getCronLimit();
        if ($limit > self::MAX_LIMIT || $limit < 1) {
            $limit = self::MAX_LIMIT;
        }

        return $limit;
    }
}
