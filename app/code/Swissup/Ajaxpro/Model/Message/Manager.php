<?php

namespace Swissup\Ajaxpro\Model\Message;

use Magento\Framework\Message\MessageInterface;

class Manager implements \Magento\Framework\Message\ManagerInterface
{
    /**
     * @var \Magento\Framework\Message\Manager
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param \Magento\Framework\Message\Manager   $messageManager
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\Message\Manager $messageManager,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->messageManager = $messageManager;
        $this->request = $request;
    }

    /**
     * @inheritdoc
     */
    public function getMessages($clear = false, $group = null)
    {
        return $this->messageManager->getMessages($clear, $group);
    }

    /**
     * @inheritdoc
     */
    public function getDefaultGroup()
    {
        return $this->messageManager->getDefaultGroup();
    }

    /**
     * @inheritdoc
     */
    public function addMessage(MessageInterface $message, $group = null)
    {
        return $this->messageManager->addMessage($message, $group);
    }

    /**
     * @inheritdoc
     */
    public function addMessages(array $messages, $group = null)
    {
        return $this->messageManager->addMessages($messages, $group);
    }

    /**
     * @inheritdoc
     */
    public function addError($message, $group = null)
    {
        return $this->messageManager->addErrorMessage($message, $group);
    }

    /**
     * @inheritdoc
     */
    public function addWarning($message, $group = null)
    {
        return $this->messageManager->addWarningMessage($message, $group);
    }

    /**
     * @inheritdoc
     */
    public function addNotice($message, $group = null)
    {
        return $this->messageManager->addNoticeMessage($message, $group);
    }

    /**
     * @inheritdoc
     */
    public function addSuccess($message, $group = null)
    {
        return $this->messageManager->addSuccessMessage($message, $group);
    }

    /**
     * @inheritdoc
     */
    public function addErrorMessage($message, $group = null)
    {
        return $this->messageManager->addErrorMessage($message, $group);
    }

    /**
     * @inheritdoc
     */
    public function addWarningMessage($message, $group = null)
    {
        return $this->messageManager->addWarningMessage($message, $group);
    }

    /**
     * @return \Magento\Framework\App\Request\Http
     */
    private function getRequest()
    {
        return $this->request;
    }

    /**
     * @inheritdoc
     */
    public function addNoticeMessage($message, $group = null)
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();
        if ($request->isAjax() && $this->isMessageToIgnore($message)) {
            return $this;
        }

        return $this->messageManager->addNoticeMessage($message, $group);
    }

    /**
     * @inheritdoc
     */
    public function addSuccessMessage($message, $group = null)
    {
        return $this->messageManager->addSuccessMessage($message, $group);
    }

    /**
     * @inheritdoc
     */
    public function addComplexErrorMessage($identifier, array $data = [], $group = null)
    {
        return $this->messageManager->addComplexErrorMessage($identifier, $data, $group);
    }

    /**
     * @inheritdoc
     */
    public function addComplexWarningMessage($identifier, array $data = [], $group = null)
    {
        return $this->messageManager->addComplexWarningMessage($identifier, $data, $group);
    }

    /**
     * @inheritdoc
     */
    public function addComplexNoticeMessage($identifier, array $data = [], $group = null)
    {
        return $this->messageManager->addComplexNoticeMessage($identifier, $data, $group);
    }

    /**
     * @inheritdoc
     */
    public function addComplexSuccessMessage($identifier, array $data = [], $group = null)
    {
        return $this->messageManager->addComplexSuccessMessage($identifier, $data, $group);
    }

    /**
     * @inheritdoc
     */
    public function addUniqueMessages(array $messages, $group = null)
    {
        return $this->messageManager->addUniqueMessages($messages, $group);
    }

    /**
     * @inheritdoc
     */
    public function addException(\Exception $exception, $alternativeText = null, $group = null)
    {
        return $this->messageManager->addException($exception, $alternativeText, $group);
    }

    /**
     * @inheritdoc
     */
    public function addExceptionMessage(\Exception $exception, $alternativeText = null, $group = null)
    {
        return $this->messageManager->addExceptionMessage($exception, $alternativeText, $group);
    }

    /**
     * @inheritdoc
     */
    public function createMessage($type, $identifier = null)
    {
        return $this->messageManager->createMessage($type, $identifier);
    }

    /**
     * Check if message is in ignore list
     *
     * @param  string|\Magento\Framework\Phrase  $message
     * @return boolean
     */
    private function isMessageToIgnore($message)
    {
        $ignoreMessages = [
            __('You need to choose options for your item.'),
            __("The product's required option(s) weren't entered. Make sure the options are entered and try again.")
        ];
        foreach ($ignoreMessages as $ignoreMessage) {
            if (strpos((string)$message, (string)$ignoreMessage) !== 1) {
                return true;
            }
        }

        return false;
    }
}
