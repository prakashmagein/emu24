<?php

namespace Swissup\Navigationpro\Block\Adminhtml\Item\Edit\Button;

use Swissup\Navigationpro\Model\Item\Locator\LocatorInterface;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Generic implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Locator
     */
    protected $locator;

    /**
     * Generic constructor
     *
     * @param Context $context
     */
    public function __construct(
        Context $context,
        LocatorInterface $locator
    ) {
        $this->context = $context;
        $this->locator = $locator;
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [];
    }

    /**
     * Return Item ID
     *
     * @return int|null
     */
    public function getItemId()
    {
        if ($item = $this->getItem()) {
            return $item->getId();
        }
        return null;
    }

    /**
     * Return Menu
     *
     * @return \Swissup\Navigationpro\Model\Item|null
     */
    public function getItem()
    {
        return $this->locator->getItem();
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
        return $this->context->getUrl($route, $params);
    }
}
