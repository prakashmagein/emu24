<?php

namespace Swissup\Gdpr\Block\Privacy;

use Magento\Framework\View\Element\Template;

class DeleteData extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Swissup_Gdpr::privacy/delete-data.phtml';

    /**
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl('privacy-tools/deletedata/post', ['_secure' => true]);
    }
}
