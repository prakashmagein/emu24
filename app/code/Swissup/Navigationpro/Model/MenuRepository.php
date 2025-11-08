<?php

namespace Swissup\Navigationpro\Model;

use \Magento\Framework\Exception\NoSuchEntityException;

class MenuRepository
{
    /**
     * @var \Swissup\Navigationpro\Model\MenuFactory
     */
    private $menuFactory;

    /**
     * @var \Swissup\Navigationpro\Model\ResourceModel\Menu
     */
    private $resource;

    /**
     * @var array
     */
    private $memo = [];

    /**
     * @param \Swissup\Navigationpro\Model\MenuFactory $menuFactory
     * @param \Swissup\Navigationpro\Model\ResourceModel\Menu $resource
     */
    public function __construct(
        \Swissup\Navigationpro\Model\MenuFactory $menuFactory,
        \Swissup\Navigationpro\Model\ResourceModel\Menu $resource
    ) {
        $this->menuFactory = $menuFactory;
        $this->resource = $resource;
    }

    /**
     * @param int $id
     * @return \Swissup\Navigationpro\Model\Menu
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        if (empty($this->memo[$id])) {
            $menu = $this->menuFactory->create();
            $this->resource->load($menu, $id);
            $this->memo[$id] = $menu;
        }

        if (!$this->memo[$id]->getId()) {
            throw new NoSuchEntityException(__('Menu with id "%1" does not exist.', $id));
        }

        return $this->memo[$id];
    }
}
