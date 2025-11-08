<?php
declare(strict_types=1);

namespace Swissup\ImageOptimizer\Model;

use Swissup\ImageOptimizer\Model\Image\Optimizers\OptimizerChainFactory;
use Symfony\Component\Process\ExecutableFinder;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckImageOptimizerExisting
{

    /**
     * @var \Swissup\ImageOptimizer\Helper\Config
     */
    private $configHelper;

    /**
     *
     * @var array
     */
    private $messages = [];

    /**
     * @var bool
     */
    private $forceLocal = false;

    /**
     * @var ExecutableFinder|null
     */
    private $executableFinder = null;

    /**
     * @var array|null
     */
    private $binaries = null;

    /**
     * @var bool
     */
    private $executed = false;

    /**
     *
     * @param \Swissup\ImageOptimizer\Helper\Config $configHelper
     */
    public function __construct(\Swissup\ImageOptimizer\Helper\Config $configHelper)
    {
        $this->configHelper = $configHelper;
    }

    /**
     * @param bool $status
     * @return $this
     */
    public function setForceLocal($status = true)
    {
        $this->forceLocal = (bool) $status;
        return $this;
    }

    /**
     * @return \Spatie\ImageOptimizer\OptimizerChain
     */
    private function getOptimizerChain()
    {
        $optimizersConfig = [];
        if ($this->configHelper->isWebPEnable()) {
            $optimizersConfig['convert_to_webp'] = true;
        }
        if ($this->configHelper->isImageOptimizersRemote() && $this->forceLocal === false) {
            $optimizersConfig['remote'] = true;
        }

        /** @var \Spatie\ImageOptimizer\OptimizerChain $optimizerChain */
        $optimizerChain = OptimizerChainFactory::create($optimizersConfig);/** @phpstan-ignore-line */
        return $optimizerChain;
    }

    /**
     * @return ExecutableFinder|null
     */
    private function getExecutableFinder()
    {
        if ($this->executableFinder === null) {
            $this->executableFinder = new ExecutableFinder();
        }

        return $this->executableFinder;
    }

    /**
     * @return array
     */
    private function getBinaries()
    {
        if ($this->binaries === null) {

            /** @var \Spatie\ImageOptimizer\OptimizerChain $optimizerChain */
            $optimizerChain = $this->getOptimizerChain();

            $optimizers = [];
            foreach($optimizerChain->getOptimizers() as $optimizer) {
                $optimizers[] = $optimizer->binaryName();
            }
            $optimizers = array_unique($optimizers);
            $optimizers = array_filter($optimizers);
            $this->binaries = $optimizers;
        }

        return $this->binaries;
    }

    /**
     * @return bool
     */
    private function isEnvironmentEmpty()
    {
        $openBaseDir = ini_get('open_basedir');
        $envPath = getenv('PATH') ?: getenv('Path');

        $dirs = $openBaseDir ? $openBaseDir : $envPath;
        return empty($dirs);
    }

    /**
     * @return void
     */
    private function execute()
    {
        if ($this->executed === true) {
            return;
        }
        $this->messages = [];

        if ($this->isEnvironmentEmpty()) {
            $this->messages[] = "If optimizer tools are instaled please verify environment variables like env." .
                "(php -i)";
            $this->executed = true;
            return;
        }

        $optimizers = $this->getBinaries();
        $executableFinder = $this->getExecutableFinder();
        foreach($optimizers as $optimizerPath) {
            $path = $executableFinder->find($optimizerPath);
            if (empty($path)) {
                $this->messages[$optimizerPath] = "{$optimizerPath} is not found (whereis {$optimizerPath})";
            }
        }
        $this->executed = true;
    }

    /**
     * @return bool
     */
    public function isAllExecutable()
    {
        $this->execute();
        return empty($this->messages);
    }

    /**
     * @return bool
     */
    public function isAllNotExecutable()
    {
        $this->execute();
        return count($this->messages) === count($this->binaries);
    }

    /**
     *
     * @return array
     */
    public function getMessages()
    {
        $this->execute();
        return $this->messages;
    }

    /**
     * @return string
     */
    public function getMainMessage()
    {
        return 'Before images can be optimized, you will need to install the optimizers as described' .
                ' https://github.com/spatie/image-optimizer#optimization-tools';
    }
}
