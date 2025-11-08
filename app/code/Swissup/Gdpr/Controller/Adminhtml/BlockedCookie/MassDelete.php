<?php

namespace Swissup\Gdpr\Controller\Adminhtml\BlockedCookie;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Swissup\Gdpr\Model\ResourceModel\BlockedCookie\CollectionFactory;

class MassDelete extends \Swissup\Gdpr\Controller\Adminhtml\AbstractController\MassDelete
{
    const ADMIN_RESOURCE = 'Swissup_Gdpr::cookieregistry';

    protected $redirectPath = '*/cookie/';

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $filter);
    }
}
