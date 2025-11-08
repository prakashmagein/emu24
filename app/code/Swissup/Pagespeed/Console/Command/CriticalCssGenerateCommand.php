<?php
declare(strict_types=1);

namespace Swissup\Pagespeed\Console\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;

class CriticalCssGenerateCommand extends \Symfony\Component\Console\Command\Command
{
    const INPUT_KEY_STORE = 'store';

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var \Swissup\Pagespeed\Model\Css\GetCriticalCss
     */
    private $service;

    /**
     * @param \Magento\Framework\App\State $appState
     * @param \Swissup\Pagespeed\Model\Css\GetCriticalCss $service
     */
    public function __construct(
        \Magento\Framework\App\State $appState,
        \Swissup\Pagespeed\Model\Css\GetCriticalCss $service
    ) {
        parent::__construct();
        $this->appState = $appState;
        $this->service = $service;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('swissup:pagespeed:criticalcss:generate')
            ->setDescription('Generate default critical css and save to config.')
            ->setAliases([
                'pagespeed:criticalcss:generate',
                'pagespeed:criticalcss:build',
                'criticalcss:generate',
                'criticalcss:build'
            ])
//            ->setHidden(true)
        ;

        $this->addOption(
            self::INPUT_KEY_STORE,
            null,
            /*InputOption::VALUE_IS_ARRAY |*/ InputOption::VALUE_OPTIONAL,
            'Store ID',
            Store::DEFAULT_STORE_ID
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->getAreaCode();
        } catch (\Exception $e) {
            $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        }

        $storeId = $input->getOption(self::INPUT_KEY_STORE);
        try {
            $this->service->setStore($storeId)
                ->generateDefault()
                ->saveConfig();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $output->writeln("<error><fg=red;options=bold>Error: {$e->getMessage()}</></error>");
        }

        $output->writeln("<info><fg=green;options=bold>Critical css generated, saved and enabled in config</></info>");

        return 0;
    }
}
