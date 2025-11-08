<?php

namespace Swissup\Easybanner\Controller\Adminhtml\Banner\Image;

class Upload extends \Magento\Catalog\Controller\Adminhtml\Category\Image\Upload
{
    const ADMIN_RESOURCE = 'Swissup_Easybanner::banner_save';

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
