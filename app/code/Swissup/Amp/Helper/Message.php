<?php
namespace Swissup\Amp\Helper;

use Magento\Framework\Message\MessageInterface;
use Magento\Theme\Controller\Result\MessagePlugin;

class Message extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\View\Element\Message\InterpretationStrategyInterface
     */
    protected $interpretationStrategy;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\View\Element\Message\InterpretationStrategyInterface $interpretationStrategy
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\View\Element\Message\InterpretationStrategyInterface $interpretationStrategy,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->messageManager = $messageManager;
        $this->cookieManager = $cookieManager;
        $this->interpretationStrategy = $interpretationStrategy;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        parent::__construct($context);
    }

    /**
     * Get messages grouped by message type
     *
     * @param boolean $clean
     * @param boolean $useMappedType
     * @param boolean $stripTags
     * @return array
     */
    public function getMessages($clean = false, $useMappedType = false, $stripTags = false)
    {
        $messages = [];
        foreach ($this->messageManager->getMessages($clean)->getItems() as $message) {
            if ($useMappedType) {
                $type = $this->getMappedType($message->getType());
            } else {
                $type = $message->getType();
            }
            $text = $message->getText();
            if ($stripTags) {
                $text = strip_tags($text, '<b><i><u><s><em><strong>');
            }
            $messages[$type][] = $text;
        }

        return $messages;
    }

    public function getMappedType($type)
    {
        $mapping = [
            MessageInterface::TYPE_WARNING => 'error',
            MessageInterface::TYPE_NOTICE  => 'error',
        ];
        if (isset($mapping[$type])) {
            return $mapping[$type];
        }

        return $type;
    }

    /**
     * Check if there are failure messages
     *
     * @param  array|null $failureTypes
     * @return boolean
     */
    public function hasFailureMessages(?array $failureTypes = null)
    {
        if (null === $failureTypes) {
            $failureTypes = [
                MessageInterface::TYPE_ERROR,
                MessageInterface::TYPE_WARNING,
                MessageInterface::TYPE_NOTICE
            ];
        }
        if (count(array_intersect(array_keys($this->getMessages()), $failureTypes))) {
            return true;
        }

        return false;
    }

    /**
     * Return messages stored in cookies
     *
     * @return array
     */
    protected function getCookiesMessages()
    {
        $messages = $this->cookieManager->getCookie(MessagePlugin::MESSAGES_COOKIES_NAME);
        if (!$messages) {
            return [];
        }

        $messages = json_decode($messages, true);
        if (!is_array($messages)) {
            $messages = [];
        }

        $metadata = $this->cookieMetadataFactory->createCookieMetadata();
        $metadata->setPath('/');
        $this->cookieManager->deleteCookie(MessagePlugin::MESSAGES_COOKIES_NAME, $metadata);

        return $messages;
    }

    /**
     * Return messages array and clean message manager messages
     *
     * @return array
     */
    public function getPageMessages()
    {
        $messages = $this->getCookiesMessages();
        foreach ($this->messageManager->getMessages(true)->getItems() as $message) {
            $messages[] = [
                'type' => $message->getType(),
                'text' => $this->interpretationStrategy->interpret($message),
            ];
        }

        return $messages;
    }
}
