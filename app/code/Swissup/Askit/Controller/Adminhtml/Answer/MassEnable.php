<?php

namespace Swissup\Askit\Controller\Adminhtml\Answer;

class MassEnable extends \Swissup\Askit\Controller\Adminhtml\Message\MassEnable
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
