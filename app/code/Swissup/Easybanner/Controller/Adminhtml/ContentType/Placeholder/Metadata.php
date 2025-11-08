<?php
declare(strict_types=1);

namespace Swissup\Easybanner\Controller\Adminhtml\ContentType\Placeholder;

use Magento\Framework\Controller\ResultFactory;

class Metadata extends \Magento\Backend\App\AbstractAction
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Swissup_Easybanner::easybanner_placeholder';

    /**
     * @var \Swissup\Easybanner\Model\ResourceModel\Placeholder\CollectionFactory
     */
    private $collectionFactory;

    /**
     * DataProvider constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Swissup\Easybanner\Model\ResourceModel\Placeholder\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Swissup\Easybanner\Model\ResourceModel\Placeholder\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);

        $this->collectionFactory = $collectionFactory;
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        try {
            $collection = $this->collectionFactory->create();
            $items = $collection
                ->addFieldToSelect(['placeholder_id', 'name', 'status'])
                ->addFieldToFilter('placeholder_id', ['eq' => $params['placeholder_id']])
                ->load();
            $result = $items->getFirstItem()->toArray();
        } catch (\Exception $e) {
            $result = [
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode()
            ];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
