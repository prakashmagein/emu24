<?php

namespace Swissup\Askit\Controller\Adminhtml\Message;

abstract class AbstractEdit extends \Magento\Backend\App\Action
{
    /**
     * @var \Swissup\Askit\Model\MessageRepository
     */
    protected $messageRepository;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Swissup\Askit\Model\MessageRepository $messageRepository
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Swissup\Askit\Model\MessageRepository $messageRepository,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Filter\FilterManager $filterManager
    ) {
        parent::__construct($context);
        $this->messageRepository = $messageRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->filterManager = $filterManager;
    }

    protected function itemNotExist()
    {
        $this->messageManager->addErrorMessage(__('This item no longer exists.'));
        /** @var \Magento\Backend\Model\View\Result\Redirect  */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param  string $text
     * @return string
     */
    protected function getShortenedText($text, $maxLength = 90)
    {
        if (!$text) {
            return $text;
        }

        $text = $this->filterManager->stripTags(
            $text,
            ['allowableTags' => false, 'escape' => null]
        );
        $text = $this->filterManager->truncate(
            $text,
            ['length' => $maxLength, 'breakWords' => false, 'etc' => '...']
        );

        return $text;
    }
}
