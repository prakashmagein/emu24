<?php
namespace Swissup\Askit\Controller\Adminhtml\Question;

use Swissup\Askit\Controller\Adminhtml\Message\AbstractGrid as MessageGrid;

class Grid extends MessageGrid
{
    /**
     * @var string
     */
    protected $gridBlockName = 'askit_question_listing';
}
