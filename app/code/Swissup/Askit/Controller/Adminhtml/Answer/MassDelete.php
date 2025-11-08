<?php
namespace Swissup\Askit\Controller\Adminhtml\Answer;

use Swissup\Askit\Model\ResourceModel\Message\Collection as AbstractCollection;
use Swissup\Askit\Controller\Adminhtml\Message\MassDelete as MessageMassDelete;

/**
 * Class MassDelete
 */
class MassDelete extends MessageMassDelete
{
    /**
     * Delete all
     *
     * @return void
     * @throws \Exception
     */
    protected function deleteAll()
    {
        /** @var \Swissup\Askit\Model\ResourceModel\Message\Collection $collection */
        $collection = $this->getCollection();
        $collection->addAnswerFilter();

        $this->setSuccessMessage($this->delete($collection));
    }
}
