<?php

namespace Swissup\Pagespeed\Installer\Command\Traits;

trait LoggerAware
{
    /**
     * @var \Psr\Log\LoggerInterface|null
     */
    protected $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @return $this
     */
    public function setLogger(?\Psr\Log\LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        if (!$this->logger) {
            $this->logger = new \Psr\Log\NullLogger();
        }
        return $this->logger;
    }
}
