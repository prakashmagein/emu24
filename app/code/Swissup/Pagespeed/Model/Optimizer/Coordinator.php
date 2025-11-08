<?php
namespace Swissup\Pagespeed\Model\Optimizer;

use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\Response\Http;

class Coordinator
{
    public function __construct(
        private PatchManager $patchManager,
        private Registry $registry,
        private Selector $selector,
        private Pipeline $pipeline
    ) {}

    public function run(ResultInterface $result, Http $response): void
    {
        $this->patchManager->apply($response);

        $optimizers = $this->selector->getApplicable($result, $this->registry->getAll());
        $this->pipeline->run($response, $optimizers);

        $this->patchManager->restore();
    }
}
