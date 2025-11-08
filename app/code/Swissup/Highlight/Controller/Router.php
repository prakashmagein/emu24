<?php

namespace Swissup\Highlight\Controller;

class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * @var \Swissup\Highlight\Helper\Page
     */
    protected $pageHelper;

    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Swissup\Highlight\Helper\Page $pageHelper
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Swissup\Highlight\Helper\Page $pageHelper
    ) {
        $this->actionFactory = $actionFactory;
        $this->pageHelper = $pageHelper;
    }

    /**
     * Validate and Match Highlight Page and modify request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $identifier = trim($request->getPathInfo(), '/');
        $type = $this->pageHelper->getPageTypeByUrlKey($identifier);
        if (!$type) {
            return false;
        }

        $request->setRouteName('highlight')
            ->setControllerName('view')
            ->setActionName($this->pageHelper->getActionName($type));
        $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
        return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
    }
}
