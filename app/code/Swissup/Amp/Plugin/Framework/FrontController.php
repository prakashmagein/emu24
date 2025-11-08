<?php
namespace Swissup\Amp\Plugin\Framework;

class FrontController
{
    /**
     * @var \Swissup\Amp\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\App\ResponseFactory
     */
    protected $responseFactory;

    /**
     * @param \Swissup\Amp\Helper\Data $helper
     * @param \Magento\Framework\App\ResponseFactory $responseFactory
     */
    public function __construct(
        \Swissup\Amp\Helper\Data $helper,
        \Magento\Framework\App\ResponseFactory $responseFactory
    ) {
        $this->helper = $helper;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param \Magento\Framework\App\FrontControllerInterface $subject
     * @param callable $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\Controller\ResultInterface|\Magento\Framework\App\Response\Http
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDispatch(
        \Magento\Framework\App\FrontControllerInterface $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        if ($this->shouldRedirect($request)) {
            $redirectionUrl = $this->helper->getAmpUrl();
            $response = $this->responseFactory->create();
            $response->setRedirect($redirectionUrl)->sendResponse();

            return $response;
        }

        return $proceed($request);
    }

    /**
     * Check if we should redirect to ?amp=1 url
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    protected function shouldRedirect($request)
    {
        return $this->helper->isAmpEnabled() &&
               $this->helper->isAmpForced() &&
               $request->getParam('amp') === null &&
               ($request->isGet() || $request->isHead());
    }
}
