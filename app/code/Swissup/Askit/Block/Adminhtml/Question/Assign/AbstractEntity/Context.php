<?php

namespace Swissup\Askit\Block\Adminhtml\Question\Assign\AbstractEntity;

use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Backend\Helper\Data as BackendHelper;
use Swissup\Askit\Model\MessageRepository;
use Swissup\Askit\Service\GetCurrentQuestionService;

class Context
{
    private $messageRepository;
    /**
     * @var TemplateContext
     */
    private $templateContext;

    /**
     * @var BackendHelper
     */
    private $backendHelper;

    /**
     * @var GetCurrentQuestionService
     */
    private $currentQuestionService;

    /**
     * @param TemplateContext           $templateContext
     * @param BackendHelper             $backendHelper
     * @param GetCurrentQuestionService $currentQuestionService
     * @param MessageRepository         $messageRepository
     */
    public function __construct(
        TemplateContext $templateContext,
        BackendHelper $backendHelper,
        GetCurrentQuestionService $currentQuestionService,
        MessageRepository $messageRepository
    ) {
        $this->templateContext = $templateContext;
        $this->backendHelper = $backendHelper;
        $this->currentQuestionService = $currentQuestionService;
        $this->messageRepository = $messageRepository;
    }

    /**
     * @return TemplateContext
     */
    public function getTemplateContext()
    {
        return $this->templateContext;
    }

    /**
     * @return BackendHelper
     */
    public function getBackendHelper()
    {
        return $this->backendHelper;
    }

    public function getMessageRepository()
    {
        return $this->messageRepository;
    }

    /**
     * @return GetCurrentQuestionService
     */
    public function getCurrentQuestionService()
    {
        return $this->currentQuestionService;
    }
}
