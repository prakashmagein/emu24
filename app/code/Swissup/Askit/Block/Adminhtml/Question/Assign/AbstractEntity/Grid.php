<?php

namespace Swissup\Askit\Block\Adminhtml\Question\Assign\AbstractEntity;

use Swissup\Askit\Model\Message;
use Swissup\Askit\Model\MessageRepository;
use Swissup\Askit\Service\GetCurrentQuestionService;

abstract class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var GetCurrentQuestionService
     */
    protected $currentQuestionService;

    /**
     * @var MessageRepository
     */
    protected $messageRepo;

    /**
     * @var Message
     */
    protected $question;

    /**
     * @param Context $context
     * @param array   $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        $this->currentQuestionService = $context->getCurrentQuestionService();
        $this->messageRepo = $context->getMessageRepository();
        parent::__construct(
            $context->getTemplateContext(),
            $context->getBackendHelper(),
            $data
        );
    }

    /**
     * @return Message
     */
    public function getQuestion()
    {
        if (!isset($this->question)) {
            $this->question = $this->currentQuestionService->getQuestion() ?:
                $this->messageRepo->create();
        }

        return $this->question;
    }
}
