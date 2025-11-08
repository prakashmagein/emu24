<?php
namespace Swissup\Amp\Controller\Catalog;

class Compare extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Catalog\Helper\Product\Compare
     */
    protected $compareHelper;

    /**
     * @var \Swissup\Amp\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Catalog\Helper\Product\Compare $compareHelper
     * @param \Swissup\Amp\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Helper\Product\Compare $compareHelper,
        \Swissup\Amp\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->compareHelper = $compareHelper;
        $this->helper = $helper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $request = $this->getRequest();
        $response = $this->getResponse();

        if (!$origin = $this->helper->validateCorsRequest($request)) {
            return $this->helper->unauthorizedResponse($response);
        }

        $result[] = [
            'isEmpty' => !$this->compareHelper->hasItems(),
            'qty' => $this->compareHelper->getItemCount()
        ];

        return $this->helper->successfulResponse($response, $origin, $result);
    }
}
