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

class ConfigureCommand extends \Symfony\Component\Console\Command\Command
{
    const INPUT_KEY_PACKAGE = 'package';
    const INPUT_KEY_STORE = 'store';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('swissup:pagespeed:configure')
            ->setDescription('Configure options.')
            ->setAliases(['swissup:pagespeed:setup', 'pagespeed:configure', 'pagespeed:setup'])
//            ->setHidden(true)
        ;

        $this->addOption(
            self::INPUT_KEY_STORE,
            null,
            InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
            'Store ID'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('marketplace:package:install');

        $arguments = [
            self::INPUT_KEY_PACKAGE => ['swissup/module-pagespeed'],
            'command' => 'marketplace:package:install',
            '--' . self::INPUT_KEY_STORE => $input->getOption(self::INPUT_KEY_STORE)
        ];
        $greetInput = new ArrayInput($arguments);

        return $command->run($greetInput, $output);
    }
}
