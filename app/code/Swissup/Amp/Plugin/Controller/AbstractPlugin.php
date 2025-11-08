<?php
namespace Swissup\Amp\Plugin\Controller;

class AbstractPlugin
{
    const SUCCESS_MESSAGE = '';

    /**
     * @var \Swissup\Amp\Helper\Data
     */
    protected $helper;

    /**
     * @var \Swissup\Amp\Helper\Message
     */
    protected $messageHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param \Swissup\Amp\Helper\Data $helper
     * @param \Swissup\Amp\Helper\Message $messageHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Swissup\Amp\Helper\Data $helper,
        \Swissup\Amp\Helper\Message $messageHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->helper = $helper;
        $this->messageHelper = $messageHelper;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
    }

    /**
     * Modify response
     *
     * @param  \Magento\Framework\App\Action\AbstractAction $subject
     * @param  mixed $originalResult
     * @return mixed
     */
    public function afterExecute(
        \Magento\Framework\App\Action\AbstractAction $subject,
        $originalResult
    ) {
        $request = $subject->getRequest();
        if (!$request->isPost() || !$request->getQuery('amp')) {
            return $originalResult;
        }

        $response = $subject->getResponse();
        if (!$origin = $this->helper->validateCorsRequest($request)) {
            return $this->helper->unauthorizedResponse($response);
        }

        if ($redirectTo = $this->getRedirectTo($response)) {
            $redirectTo = str_replace('http://', 'https://', $redirectTo);
            $response->setHeader('AMP-Redirect-To', $redirectTo);
        }

        $result = [
            'success'  => true,
            'messages' => []
        ];

        if ($this->messageHelper->hasFailureMessages()) {
            $result['success']  = false;
            $result['messages'] = $this->messageHelper->getMessages(false, true, true);
        } else {
            $result['messages']['success'] = [$this->getSuccessMessage($request)];
        }
        $this->messageHelper->getPageMessages();

        return $this->helper->successfulResponse(
            $response, $origin, $result, $result['success'] ? 200 : 400
        );
    }

    /**
     * Load product by id
     *
     * @param  int $id
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    protected function getProductById($id)
    {
        return $this->productRepository->getById(
            (int)$id,
            false,
            $this->storeManager->getStore()->getId()
        );
    }

    /**
     * Get success message for the action
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return string
     */
    protected function getSuccessMessage($request)
    {
        $product = $this->getProductById($request->getParam('product'));
        return __(static::SUCCESS_MESSAGE, $product->getName());
    }

    /**
     * Get redirect to url
     *
     * @param \Magento\Framework\App\ResponseInterface
     * @return string|bool
     */
    protected function getRedirectTo($response)
    {
        return $this->helper->getRedirectTo($response);
    }
}
