<?php

namespace Swissup\Easybanner\Block\Adminhtml;

use Magento\Backend\Block\Widget\Context;

class AbstractButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var string
     */
    protected $idParamName;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->context->getRequest()->getParam($this->getIdParamName());
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }

    /**
     * @return string
     */
    protected function getIdParamName()
    {
        return $this->idParamName;
    }
}
