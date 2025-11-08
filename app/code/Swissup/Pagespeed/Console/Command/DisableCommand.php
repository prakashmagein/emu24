<?php
declare(strict_types=1);

namespace Swissup\Pagespeed\Console\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Config\Console\Command\ConfigSetCommand;
use Magento\Framework\App\Config\ScopeConfigInterface;

class DisableCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('swissup:pagespeed:disable')
            ->setDescription('Fast disable pagespeed module.')
            ->setDefinition([
                new InputOption(
                    ConfigSetCommand::OPTION_SCOPE,
                    null,
                    InputArgument::OPTIONAL,
                    'Configuration scope (default, website, or store)',
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                ),
                new InputOption(
                    ConfigSetCommand::OPTION_SCOPE_CODE,
                    null,
                    InputArgument::OPTIONAL,
                    'Scope code (required only if scope is not \'default\')'
                )
            ])
            ->setAliases(['pagespeed:disable'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = 'config:set';
        $path = 'pagespeed/main/enable';
        $value = 0;

        $command = $this->getApplication()->find($command);

        $arguments = [
            'command' => $command,
            ConfigSetCommand::ARG_PATH => $path,
            ConfigSetCommand::ARG_VALUE => $value,
        ];

        $optionValue = $input->getOption(ConfigSetCommand::OPTION_SCOPE);
        if (!empty($optionValue)) {
            $arguments['--' . ConfigSetCommand::OPTION_SCOPE] = $optionValue;
        }
        $optionValue = $input->getOption(ConfigSetCommand::OPTION_SCOPE_CODE);
        if (!empty($optionValue)) {
            $arguments['--' . ConfigSetCommand::OPTION_SCOPE_CODE] = $optionValue;
        }

        $greetInput = new ArrayInput($arguments);

        return $command->run($greetInput, $output);
    }
}
