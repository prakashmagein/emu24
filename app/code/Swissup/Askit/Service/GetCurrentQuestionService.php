<?php
namespace Swissup\Askit\Service;

use Swissup\Askit\Api\Data\MessageInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class GetCurrentQuestionService
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    private $session;

    /**
     * @var \Swissup\Askit\Model\MessageRepository
     */
    private $messageRepository;

    /**
     *
     * @var int|null
     */
    private $questionId;

    /**
     *
     * @var MessageInterface|null
     */
    private $currentQuestion;

    /**
     * @param \Magento\Backend\Model\Session $session
     * @param \Swissup\Askit\Model\MessageRepository $messageRepository
     */
    public function __construct(
        \Magento\Backend\Model\Session $session,
        \Swissup\Askit\Model\MessageRepository $messageRepository
    ) {
        $this->session = $session;
        $this->messageRepository = $messageRepository;
    }

    /**
     * @return int|null
     */
    public function getQuestionId()
    {
        if (!$this->questionId) {
            $currentCategoryId = $this->session->getData('askit_question_id');
            if ($currentCategoryId) {
                $this->questionId =  (int) $currentCategoryId;
            }
        }
        return $this->questionId;
    }

    /**
     * @return MessageInterface|null
     */
    public function getQuestion(): ?MessageInterface
    {
        if (!$this->currentQuestion) {
            $questionId = $this->getQuestionId();
            if (!$questionId) {
                return null;
            }
            try {
                $this->currentQuestion = $this->messageRepository->get($questionId);
            } catch (NoSuchEntityException $e) {
                return null;
            }
        }
        return $this->currentQuestion;
    }
}