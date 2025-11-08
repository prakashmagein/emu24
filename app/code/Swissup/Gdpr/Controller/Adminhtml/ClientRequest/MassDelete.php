<?php

namespace Swissup\Gdpr\Controller\Adminhtml\ClientRequest;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Swissup\Gdpr\Model\ResourceModel\ClientRequest\CollectionFactory;

class MassDelete extends \Swissup\Gdpr\Controller\Adminhtml\AbstractController\MassDelete
{
    const ADMIN_RESOURCE = 'Swissup_Gdpr::clientrequest_delete';

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
