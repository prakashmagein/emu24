<?php
namespace Swissup\Askit\Controller\Customer;

use \Magento\Framework\App\RequestInterface;
use \Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\UrlFactory
     */
    private $customerUrlFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\UrlFactory $customerUrlFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\UrlFactory $customerUrlFactory
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->customerUrlFactory = $customerUrlFactory;
    }

    /**
     * Return login URL
     *
     * @return string
     */
    private function getLoginUrl()
    {
        /** @var \Magento\Customer\Model\Url $customerModelUrl */
        $customerModelUrl = $this->customerUrlFactory->create();
        return $customerModelUrl->getLoginUrl();
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->getLoginUrl();

        if (!$this->customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        /** @var \Magento\Framework\View\Element\AbstractBlock $navigationBlock */
        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('questions/customer');
        }
        /** @var \Magento\Framework\View\Element\AbstractBlock $block */
        $block = $resultPage->getLayout()->getBlock('askit_customer_listing');
        if ($block) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }
        $resultPage->getConfig()->getTitle()->set(__('My Questions'));
        return $resultPage;
    }
}
