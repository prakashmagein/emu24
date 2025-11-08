<?php
namespace Swissup\Amp\Controller\Checkout;

class Cart extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $cartHelper;

    /**
     * @var \Swissup\Amp\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Swissup\Amp\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Swissup\Amp\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->cartHelper = $cartHelper;
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
            'isEmpty' => $this->cartHelper->getItemsQty() == 0,
            'qty' => $this->cartHelper->getItemsQty()
        ];

        return $this->helper->successfulResponse($response, $origin, $result);
    }
}
