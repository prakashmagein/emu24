<?php
namespace Swissup\Amp\Controller\Cookie;

class Show extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Cookie\Helper\Cookie
     */
    protected $cookieHelper;

    /**
     * @var \Swissup\Amp\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Cookie\Helper\Cookie $cookieHelper
     * @param \Swissup\Amp\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Cookie\Helper\Cookie $cookieHelper,
        \Swissup\Amp\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->cookieHelper = $cookieHelper;
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

        $result = [
            'showNotification' => $this->cookieHelper->isUserNotAllowSaveCookie() &&
                $this->helper->cookieRestriction()
        ];

        return $this->helper->successfulResponse($response, $origin, $result);
    }
}
