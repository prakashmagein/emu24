<?php
namespace Swissup\Askit\Helper;

use Swissup\Askit\Api\Data\MessageInterface;
use Swissup\Askit\Model\ResourceModel\Post\Collection as PostCollection;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Helper\AbstractHelper;

class Message extends AbstractHelper
{
    /**
     * @var \Swissup\Askit\Model\Message|\Swissup\Askit\Api\Data\MessageInterface
     */
    private $message;

    /**
     * @var \Swissup\Askit\Model\MessageRepository
     */
    private $messageRepository;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Swissup\Askit\Model\Message $message
     * @param \Swissup\Askit\Model\MessageRepository $messageRepository
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Swissup\Askit\Model\Message $message,
        \Swissup\Askit\Model\MessageRepository $messageRepository,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->message = $message;
        $this->messageRepository = $messageRepository;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Return an askit item from given question id.
     *
     * @param Action $action
     * @param null $questionId
     * @return \Magento\Framework\View\Result\Page|bool
     */
    public function prepareResultItem(Action $action, $questionId = null)
    {
        if ($questionId !== null && $questionId !== $this->message->getId()) {
            $delimiterPosition = strrpos($questionId, '|');
            if ($delimiterPosition) {
                $questionId = substr($questionId, 0, $delimiterPosition);
            }
            $this->message = $this->messageRepository->get($questionId);
        }

        if (!$this->message->getId()) {
            return false;
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        // We can add our own custom page handles for layout easily.
        $resultPage->addHandle('askit_message_view');

        // This will generate a layout handle like: askit_message_view_id_1
        // giving us a unique handle to target specific blog question if we wish to.
        $resultPage->addPageLayoutHandles(['id' => $this->message->getId()]);

        // Magento is event driven after all, lets remember to dispatch our own, to help people
        // who might want to add additional functionality, or filter the question somehow!
        $this->_eventManager->dispatch(
            'swissup_askit_message_render',
            ['message' => $this->message, 'controller_action' => $action]
        );

        return $resultPage;
    }
}
