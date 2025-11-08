<?php

namespace Swissup\Navigationpro\Api\Data;

interface MenuInterface
{
    const MENU_ID       = 'menu_id';
    const IDENTIFIER    = 'identifier';
    const IS_ACTIVE     = 'is_active';

    /**
     * Get ID
     *
     * @return int
     */
    public function getId();

    /**
     * Get menu identifier
     *
     * @return string
     */
    public function getIdentifier();

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
     * Set menu identifier
     *
     * @param  string $identifier
     * @return MenuInterface
     */
    public function setIdentifier($identifier);

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
