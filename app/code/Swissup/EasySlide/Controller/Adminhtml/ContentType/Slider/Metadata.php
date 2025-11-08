<?php
declare(strict_types=1);

namespace Swissup\EasySlide\Controller\Adminhtml\ContentType\Slider;

use Magento\Framework\Controller\ResultFactory;

class Metadata extends \Magento\Backend\App\AbstractAction
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Swissup_EasySlide::easyslide_slider';

    /**
     * @var \Swissup\EasySlide\Model\ResourceModel\Slider\CollectionFactory
     */
    private $collectionFactory;

    /**
     * DataProvider constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Swissup\EasySlide\Model\ResourceModel\Slider\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Swissup\EasySlide\Model\ResourceModel\Slider\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);

        $this->collectionFactory = $collectionFactory;
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        try {
            $collection = $this->collectionFactory->create();
            $blocks = $collection
                ->addFieldToSelect(['title', 'is_active'])
                ->addFieldToFilter('identifier', ['eq' => $params['identifier']])
                ->load();
            $result = $blocks->getFirstItem()->toArray();
        } catch (\Exception $e) {
            $result = [
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode()
            ];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
