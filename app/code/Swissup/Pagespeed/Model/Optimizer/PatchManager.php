<?php
namespace Swissup\Pagespeed\Model\Optimizer;

use Magento\Framework\App\Response\Http;
use Swissup\Pagespeed\Model\Patch\PatcherInterface;

class PatchManager
{
    private array $patches;
    private array $instances = [];

    public function __construct(array $patches = [])
    {
        $this->patches = $patches;
    }

    public function apply(Http $response): void
    {
        foreach ($this->patches as $patch) {
            $this->get($patch)->apply($response);
        }
    }

    public function restore(): void
    {
        foreach ($this->patches as $patch) {
            $this->get($patch)->restore();
        }
    }

    private function get($factory): PatcherInterface
    {
        $hash = get_class($factory);
        return $this->instances[$hash] ??= $factory->create();
    }
}
