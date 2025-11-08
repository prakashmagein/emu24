<?php

namespace Swissup\Ajaxpro\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends Config
{
    /**
     *
     * @return string
     */
    public function getCartPopupClassName()
    {
        $classNames = [
            'ajaxpro-modal-dialog',
            'ajaxpro-modal-dialog-' . $this->getCartDialogType()
        ];

        $handle = $this->getCartHandle();
        $classNames[] = str_replace(['_', ' '], '-', $handle);

        return implode(' ', $classNames);
    }
}
