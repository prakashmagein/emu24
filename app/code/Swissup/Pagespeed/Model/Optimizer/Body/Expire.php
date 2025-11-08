<?php
namespace Swissup\Pagespeed\Model\Optimizer\Body;

use Swissup\Pagespeed\Helper\Config;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\App\Request\Http as RequestHttp;
use Swissup\Pagespeed\Model\Optimizer\AbstractOptimizer;

class Expire extends AbstractOptimizer
{
    /**
     * Request
     *
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Customer session model
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @param Config $config
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Config $config,
        RequestHttp $request,
        Session $customerSession
    ) {
        $this->request = $request;
        $this->customerSession = $customerSession;
        parent::__construct($config);
    }

    /**
     * Perform result postprocessing
     *
     * @param ResponseHttp $response
     * @return ResponseHttp
     */
    public function process(?ResponseHttp $response = null)
    {
        if (!$this->config->isAddExpireEnable()
            || $response === null
            || $response->isRedirect()
        ) {
            return $response;
        }

        $request = $this->request;

        if ((!$request->isGet() && !$request->isHead())
            || $request->isAjax()
        ) {
            return $response;
        }

        $handle = $request->getFullActionName();
        $handles = [
            'cms_index_index',
            'cms_page_view',
            'catalog_category_view',
            'catalog_product_view'
        ];

        if (!in_array($handle, $handles)
            || $this->config->isVarnishEnabled()
        ) {
            return $response;
        }

        $ttl = $this->config->getExpireTTL();
        if (!$this->customerSession->isLoggedIn()) {
            $response->setPublicHeaders($ttl);
//            } else {
//                $response->setPrivateHeaders($ttl);
        }
        //setNoCacheHeaders

        return $response;
    }
}
