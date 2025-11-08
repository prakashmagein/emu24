<?php
namespace Swissup\Askit\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

class Index implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    private $resultFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->resultFactory = $resultFactory;
        $this->customerSession = $customerSession;
        $this->request = $request;
    }

    /**
     * Default customer account page
     *
     * @inherit
     */
    public function execute()
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();
        if ($request->isAjax()) {
            $askitExpanded = $this->customerSession->getAskitExpanded();
            if ($askitExpanded !== null) {
                $request->setParam('expanded', $askitExpanded);
                $this->customerSession->unsAskitExpanded();
            }

            /** @var \Magento\Framework\View\Result\Layout $resultPage */
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
            $resultPage->addHandle('askit_question_listajax');
            $request->setQueryValue('ajax', null);
        } else {
            /** @var \Magento\Framework\Controller\ResultInterface $resultPage */
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        }
        return $resultPage;
    }

    /**
     * Retrieve request object
     *
     * @return \Magento\Framework\App\Request\Http
     */
    private function getRequest()
    {
        return $this->request;
    }
}
