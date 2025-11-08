<?php
namespace Swissup\Askit\Controller;

use Swissup\Askit\Helper\Config;
use Swissup\Askit\Model\UrlEntityDataResolver;

class Router implements \Magento\Framework\App\RouterInterface
{
    const ROUTE_PREFIX = 'questions';

    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * @var Config
     */
    private $configHelper;

    /**
     * @var \Swissup\Askit\Service\UrlEntityDataResolverFactory
     */
    private $urlEntityDataResolverFactory;

    /**
     * @var \Swissup\Askit\Service\IsItemHasPublicQuetionsFactory
     */
    private $isItemHasPublicQuetionsFactory;

    /**
     * @var string
     */
    private $moduleName = 'askit';

    /**
     * @var string
     */
    private $controllerName = 'index';

    /**
     * @var string
     */
    private $actionName = 'index';

    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param Config $configHelper
     * @param \Swissup\Askit\Service\UrlEntityDataResolverFactory $urlEntityDataResolverFactory
     * @param \Swissup\Askit\Service\IsItemHasPublicQuetionsFactory $isItemHasPublicQuetionsFactory
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        Config $configHelper,
        \Swissup\Askit\Service\UrlEntityDataResolverFactory $urlEntityDataResolverFactory,
        \Swissup\Askit\Service\IsItemHasPublicQuetionsFactory $isItemHasPublicQuetionsFactory
    ) {
        $this->actionFactory = $actionFactory;
        $this->configHelper = $configHelper;
        $this->urlEntityDataResolverFactory = $urlEntityDataResolverFactory;
        $this->isItemHasPublicQuetionsFactory = $isItemHasPublicQuetionsFactory;
    }

    /**
     * Validate and Match and modify request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface|null
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->configHelper->isEnabled()) {
            return null;
        }
        $urlPath = (string) $request->getPathInfo();
        $urlPath = trim($urlPath, '/');

        if (strpos($urlPath, self::ROUTE_PREFIX) !== 0) {
            return null;
        }

        $identifier = substr($urlPath, strlen(self::ROUTE_PREFIX));
        $identifier = trim($identifier, '/');

        $urlEntityData = [];
        if (strpos($identifier, 'customer/') === 0 || $identifier === 'customer') {
            $this->controllerName = 'customer';
        } elseif (!empty($identifier)) {
            $urlEntityData = [];
            if ($this->configHelper->isAllowedPageEntityQuestions()) {
                $urlEntityData = $this->urlEntityDataResolverFactory->create()
                    ->resolve($identifier);
            }

            if (empty($urlEntityData)) {
                return null;
            }

            if ($this->configHelper->isPageEntityQuestionsRequiresData()) {
                $hasQuestions = $this->isItemHasPublicQuetionsFactory->create()
                    ->has(
                        $urlEntityData['id'] ?? $urlEntityData['page_id'],
                        $urlEntityData['item_type_id']
                    );
                if (!$hasQuestions) {
                    return null;
                }
            }
        }

        $request->setModuleName($this->moduleName)
            ->setControllerName($this->controllerName)
            ->setActionName($this->actionName);

        foreach($urlEntityData as $paramName => $paramValue) {
             $request->setParam($paramName, $paramValue);
        }

        $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $urlPath);

        /** @var \Magento\Framework\App\ActionInterface $forwardAction */
        $forwardAction = $this->actionFactory->create(\Magento\Framework\App\Action\Forward::class);
        return $forwardAction;
    }
}
