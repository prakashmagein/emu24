<?php

namespace Swissup\Attributepages\Controller\Adminhtml\Image;

class Upload extends \Magento\Catalog\Controller\Adminhtml\Category\Image\Upload
{
    const ADMIN_RESOURCE = 'Swissup_Attributepages::option_save';

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
