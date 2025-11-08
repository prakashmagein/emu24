<?php
namespace Swissup\Askit\Controller\Message;

abstract class SaveAbstract //extends \Magento\Framework\App\Action\Action
    implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Swissup\Askit\Helper\Config
     */
    protected $configHelper;

    /**
     * @var \Swissup\Askit\Model\MessageRepository
     */
    protected $messageRepository;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Swissup\Askit\Model\Message\Validator
     */
    private $validator;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Swissup\Askit\Helper\Config $configHelper
     * @param \Swissup\Askit\Model\MessageRepository $messageRepository
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Swissup\Askit\Model\Message\Validator $validator
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Swissup\Askit\Helper\Config $configHelper,
        \Swissup\Askit\Model\MessageRepository $messageRepository,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Swissup\Askit\Model\Message\Validator $validator
    ) {
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->configHelper = $configHelper;
        $this->messageRepository = $messageRepository;
        $this->formKeyValidator = $formKeyValidator;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
        $this->request = $request;
        $this->eventManager = $eventManager;
        $this->validator = $validator;
    }

    /**
     *
     */
    protected function redirectReferer()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setRefererOrBaseUrl();
        // $this->_redirect($this->_redirect->getRedirectUrl());
    }

    /**
     * @return bool
     */
    protected function validateFormKey()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(
                __('Your session has expired')
            );

            return false;
        }

        return true;
    }

    /**
     * Retrieve request object
     *
     * @return \Magento\Framework\App\Request\Http|\Laminas\Http\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function resultJson()
    {
        $messages = $this->messageManager->getMessages(true);
        $response = [];
        foreach ($messages->getItems() as $message) {
            $response['messages'][] = [
                'type' => $message->getType(),
                'text' => $message->getText()
            ];
        }

        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData($response);
    }

    /**
     * @param array $data
     * @return void
     * @throws \Magento\Framework\Validator\Exception
     */
    protected function validateData($data)
    {
        $this->validator->validate($data);
    }
}
