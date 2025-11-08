<?php
namespace Swissup\Ajaxpro\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Controller\Result\Json;

class RedirectResultToJsonResultObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonResultFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     */
    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
    ) {
        $this->jsonResultFactory = $jsonResultFactory;
    }

    /**
     * Replace no json result with json when request is ajax
     * @see \Magento\Theme\Controller\Result\MessagePlugin::afterRenderResult
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $event->getRequest();
        
        /** @var \Magento\Framework\DataObject $resultObject */
        $resultObject = $event->getResultObject();
        $result = $resultObject->getData('result');
        if ($request->isAjax() && !($result instanceof Json)) {
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->jsonResultFactory->create();
            $resultJsonData = [
                'action' => $request->getFullActionName()
            ];
            $resultJson->setData($resultJsonData);
            $resultObject->setData('result', $resultJson);
        }
        return $this;
    }
}
