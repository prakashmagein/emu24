<?php
namespace Swissup\Attributepages\Controller;

class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @var \Swissup\Attributepages\Model\PageParamsExtractor
     */
    private $pageParamsExtractor;

    /**
     * Page view helper
     *
     * @var \Swissup\Attributepages\Helper\Page\View
     */
    protected $pageViewHelper;

    /**
     * @var \Swissup\Attributepages\Helper\Data
     */
    protected $attrpagesHelper;

    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\ResponseInterface $response,
        \Swissup\Attributepages\Model\PageParamsExtractor $pageParamsExtractor,
        \Swissup\Attributepages\Helper\Page\View $pageViewHelper,
        \Swissup\Attributepages\Helper\Data $attrpagesHelper
    ) {
        $this->actionFactory = $actionFactory;
        $this->response = $response;
        $this->pageParamsExtractor = $pageParamsExtractor;
        $this->pageViewHelper = $pageViewHelper;
        $this->attrpagesHelper = $attrpagesHelper;
    }
    /**
     * Validate and Match Attribute Page and modify request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $params = $this->pageParamsExtractor->extract();
        $page = $this->pageViewHelper->initPagesInRegistry(
            isset($params[1]) ? $params[1] : $params[0], // current_page
            isset($params[1]) ? $params[0] : false,      // parent_page
            'identifier'
        );

        if (!$page) {
            return false;
        }

        $pathInfo = $request->getPathInfo();
        $trimmedPathInfo = trim($pathInfo, '/');
        $pathParts = explode('/', $trimmedPathInfo);
        $redirect = substr($pathInfo, -1) === '/' || count($pathParts) > 2;

        if (!$redirect) {
            $redirect = strpos($page->getUrl(), $pathInfo) === false;
        }

        if (!$redirect) {
            $suffix = $this->attrpagesHelper->getUrlSuffix();
            $suffixIndex = $this->attrpagesHelper->getSuffixIndex($pathInfo);
            if ($suffix && !$suffixIndex) {
                $redirect = true;
            }
        }

        if ($redirect) {
            $this->response->setRedirect($page->getUrl(), 301);
            $request->setDispatched(true);
            return $this->actionFactory->create(\Magento\Framework\App\Action\Redirect::class);
        }

        $request->setRouteName('attributepages')
            ->setControllerName('page')
            ->setActionName('view')
            ->setParam('page_id', $page->getId());

        $parent = $this->pageViewHelper
            ->getRegistryObject('attributepages_parent_page');
        if ($parent) {
            $request->setParam('parent_id', $parent->getId());
        }

        $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $trimmedPathInfo);
        return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
    }
}
