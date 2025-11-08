<?php

namespace Swissup\Gdpr\Controller\Adminhtml\Cookie;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Swissup\Gdpr\Model\ResourceModel\Cookie\CustomCollectionFactory;

class MassDelete extends \Swissup\Gdpr\Controller\Adminhtml\AbstractController\MassDelete
{
    const ADMIN_RESOURCE = 'Swissup_Gdpr::cookieregistry';

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CustomCollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CustomCollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $filter);
    }
}
