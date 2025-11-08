<?php

namespace Swissup\Askit\Block\Question;

class View extends AbstractBlock
{
    protected $question;

    public function setQuestion($question)
    {
        $this->question = $question;
        return $this;
    }

    public function getQuestion()
    {
        return $this->question;
    }
}
