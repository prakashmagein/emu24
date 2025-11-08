<?php

namespace Swissup\Navigationpro\Api\Data;

interface ItemInterface
{
    const ITEM_ID       = 'item_id';
    const MENU_ID       = 'menu_id';
    const NAME          = 'name';
    const URL_PATH      = 'url_path';
    const IS_ACTIVE     = 'is_active';

    /**
     * Get ID
     *
     * @return int
     */
    public function getId();

    /**
     * Get Menu ID
     *
     * @return int
     */
    public function getMenuId();

    /**
     * Get menu identifier
     *
     * @return string
     */
    public function getName();

    /**
     * Get is_active
     *
     * @return bool
     */
    public function getIsActive();

    /**
     * Set ID
     *
     * @param  int $id
     * @return MenuInterface
     */
    public function setId($id);

    /**
     * Set Menu ID
     *
     * @param  int $id
     * @return MenuInterface
     */
    public function setMenuId($id);

    /**
     * Set menu identifier
     *
     * @param  string $name
     * @return MenuInterface
     */
    public function setName($name);

    /**
     * Set is_active
     *
     * @param  int $isActive
     * @return MenuInterface
     */
    public function setIsActive($isActive);

    /**
     * Is active
     *
     * @return bool
     */
    public function isActive();
}
