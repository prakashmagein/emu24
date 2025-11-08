<?php
namespace Swissup\Easybanner\Controller\Adminhtml\Banner;

use Magento\Backend\App\Action;

abstract class Widget extends Action
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Swissup_Easybanner::promo_banner');
    }
}
