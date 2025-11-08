<?php
namespace Swissup\Pagespeed\Model\Optimizer;

class Registry
{
    private array $factories;

    private array $instances = [];

    public function __construct(array $optimizers)
    {
        $this->factories = $optimizers;
    }

    /**
     * Get specific optimizer instance
     *
     * @param string $key
     * @return OptimizerInterface
     * @throws \InvalidArgumentException
     */
    public function get(string $key): OptimizerInterface
    {
        if (!isset($this->instances[$key])) {
            if (!isset($this->factories[$key])) {
                throw new \InvalidArgumentException(sprintf('Optimizer "%s" is not registered', $key));
            }
            $this->instances[$key] = $this->factories[$key]->create();
        }

        return $this->instances[$key];
    }

    /**
     * Get all optimizer instances
     *
     * @return OptimizerInterface[]
     */
    public function getAll(): array
    {
        foreach ($this->factories as $key => $factory) {
            if (!isset($this->instances[$key])) {
                $this->get($key);
            }
        }

        return $this->instances;
    }

    public function has(string $key): bool
    {
        return isset($this->factories[$key]);
    }

    public function getKeys(): array
    {
        return array_keys($this->factories);
    }
}
