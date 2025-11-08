<?php

namespace Swissup\Askit\Controller\Adminhtml\Answer;

class MassDisable extends \Swissup\Askit\Controller\Adminhtml\Message\MassDisable
{
    /**
     * @{inheritdocs}
     */
    protected function getCollection()
    {
        /** @var \Swissup\Askit\Model\ResourceModel\Message\Collection $collection */
        $collection = parent::getCollection();

        return $collection->addAnswerFilter();
    }
}
