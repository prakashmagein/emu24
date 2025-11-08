<?php

namespace Swissup\Navigationpro\Model\Menu\Locator;

use Swissup\Navigationpro\Model\Menu;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Registry;

class DefaultLocator implements LocatorInterface
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var MenuFactory
     */
    protected $menuFactory;

    /**
     * @var \Swissup\Navigationpro\Model\Menu
     */
    private $menu;

    /**
     * @param Registry $registry
     */
    public function __construct(
        Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        \Swissup\Navigationpro\Model\MenuFactory $menuFactory
    ) {
        $this->registry = $registry;
        $this->request = $request;
        $this->menuFactory = $menuFactory;
    }

    /**
     * {@inheritdoc}
     * @throws NotFoundException
     */
    public function getMenu()
    {
        if (null !== $this->menu) {
            return $this->menu;
        }

        if ($menu = $this->registry->registry('navigationpro_menu')) {
            return $this->menu = $menu;
        }

        if ($id = $this->request->getParam('menu_id')) {
            $menu = $this->menuFactory->create();
            $menu->load($id);
            return $this->menu = $menu;
        }

        throw new NotFoundException(__('Menu was not registered'));
    }
}
