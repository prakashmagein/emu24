<?php

namespace Swissup\Askit\Block\Question\Answer;

use Swissup\Askit\Block\Question;

class View extends \Swissup\Askit\Block\Question\AbstractBlock
{
    /**
     * @var \Swissup\Askit\Model\Message
     */
    protected $answer;

    /**
     * @param $answer
     * @return $this
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;
        return $this;
    }

    /**
     * @return \Swissup\Askit\Model\Message
     */
    public function getAnswer()
    {
        return $this->answer;
    }
}
