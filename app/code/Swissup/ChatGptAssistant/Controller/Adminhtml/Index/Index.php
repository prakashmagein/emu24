<?php
namespace Swissup\ChatGptAssistant\Controller\Adminhtml\Index;

class Index extends \Magento\Backend\App\Action
{
    /**
     * Admin resource
     */
    const ADMIN_RESOURCE = 'Swissup_ChatGptAssistant::index';

    private \Magento\Framework\Serialize\Serializer\Json $jsonEncoder;

    private \Swissup\ChatGptAssistant\Model\ChatGptRequestFactory $requestFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Serialize\Serializer\Json $jsonEncoder,
        \Swissup\ChatGptAssistant\Model\ChatGptRequestFactory $requestFactory
    ) {
        parent::__construct($context);
        $this->jsonEncoder = $jsonEncoder;
        $this->requestFactory = $requestFactory;
    }

    public function execute()
    {
        $prompt = $this->getRequest()->getParam('prompt');
        $result = $this->requestFactory->create()->sendRequest($prompt);

        return $this->getResponse()->setHttpResponseCode($result['code'])->setBody(
            $this->jsonEncoder->serialize([
                'result' => $result['result']
            ])
        );
    }
}
