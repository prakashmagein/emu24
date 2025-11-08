<?php

namespace Swissup\Pagespeed\Installer\Command;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

use Swissup\Pagespeed\Installer\Command\Traits\LoggerAware;

class Cleanup
{
    use LoggerAware;

    /**
     * @var \Magento\Framework\App\State\CleanupFilesFactory
     */
    private $cleanupFactory;

    /**
     *
     * @param \Magento\Framework\App\State\CleanupFilesFactory $cleanupFactory
     */
    public function __construct(\Magento\Framework\App\State\CleanupFilesFactory $cleanupFactory)
    {
        $this->cleanupFactory = $cleanupFactory;
    }

    /**
     * @param \Swissup\Marketplace\Installer\Request $request
     * @return void
     */
    public function execute($request)
    {
        $cleanupFiles = $this->cleanupFactory->create();
        $output = $this->getLogger();

//        $cleanupFiles->clearCodeGeneratedClasses();
//        $output->info(
//            "Generated classes cleared successfully. Please run the 'setup:di:compile' command to generate classes."
//        );
        $cleanupFiles->clearMaterializedViewFiles();
        $output->info(
            "Generated static view files cleared successfully. Please run the 'setup:static-content:deploy' command to deploy static view files"
        );
    }
}
