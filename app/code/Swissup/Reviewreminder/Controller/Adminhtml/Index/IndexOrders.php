<?php
namespace Swissup\Reviewreminder\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;

class IndexOrders extends \Magento\Backend\App\Action
{
    const PAGE_SIZE = 10;
    /**
     * Json encoder
     *
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;
    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;
    /**
     * Get extension configuration helper
     * @var \Swissup\Reviewreminder\Helper\Config
     */
    protected $configHelper;
    /**
     * Get extension helper
     * @var \Swissup\Reviewreminder\Helper\Helper
     */
    protected $helper;
    /**
     * @param Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Swissup\Reviewreminder\Helper\Config $configHelper
     * @param \Swissup\Reviewreminder\Helper\Helper $helper
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Framework\App\ResourceConnection $resource,
        \Swissup\Reviewreminder\Helper\Config $configHelper,
        \Swissup\Reviewreminder\Helper\Helper $helper
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->mathRandom = $mathRandom;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->configHelper = $configHelper;
        $this->helper = $helper;
        parent::__construct($context);
    }
    /**
     * Index orders action
     *
     */
    public function execute()
    {
        $stores = explode(',', $this->getRequest()->getParam('stores'));
        if (count($stores) == 0 || trim($stores[0]) == '') {
            return $this->getResponse()->setBody($this->jsonEncoder->encode(array(
                'error' => __('Please select store view(s)')
            )));
        }
        $fromDateType = $this->getRequest()->getParam('from_date_type');
        switch ($fromDateType) {
            case 1:
                $timestamp = strtotime('-1 year');
            break;
            case 2:
                $timestamp = strtotime('-1 month');
            break;
            case 3:
                $timestamp = strtotime('-1 week');
            break;
            case 4:
                $fromDateStr = $this->getRequest()->getParam('from_date');
                if (($timestamp = strtotime($fromDateStr)) === false) {
                    return $this->getResponse()->setBody($this->jsonEncoder->encode(array(
                        'error' => __('Please enter correct date in YYYY-MM-DD format')
                    )));
                }
            break;
        }
        $fromDate = date('Y-m-d', $timestamp);
        $lastProcessed = $this->getRequest()->getParam('last_processed', 0);
        $pageSize = $this->getRequest()->getParam('page_size', self::PAGE_SIZE);
        /* @var $orderModel \Magento\Sales\Model\Order */
        $orderModel = $this->_objectManager->create('Magento\Sales\Model\Order');
        $orders = $orderModel
            ->getCollection()
            ->addAttributeToFilter('entity_id', array('gt' => $lastProcessed))
            ->addAttributeToFilter('created_at', array('from'=>$fromDate))
            ->setPageSize($pageSize)
            ->setCurPage(1);
        if (count($stores) > 1 || $stores[0] != '0') {
            $orders->addAttributeToFilter('store_id', array('in' => $stores));
        }
        if ($this->configHelper->allowSpecificStatuses()) {
            $orders->addAttributeToFilter('status',
                array('in' => $this->configHelper->specificOrderStatuses()));
        }
        $indexedOrdersIds = $this->_objectManager->create('Swissup\Reviewreminder\Model\Entity')
            ->getCollection()
            ->getColumnValues('order_id');
        $newOrderIds = $orders->getAllIds();
        $orderIdsDiff = array_diff($newOrderIds, $indexedOrdersIds);
        if (count($orderIdsDiff) > 0) {
            $ordersData = array();
            foreach ($orderIdsDiff as $id) {
                $order = $orderModel->load($id);
                $customerEmail = $order->getCustomerEmail();
                $orderDate = $this->helper->getOrderDate($order, $this->configHelper);
                $ordersData[] = array(
                    'order_id' => $id,
                    'customer_email' => $customerEmail,
                    'order_date' => $orderDate,
                    'status' => $this->configHelper->getDefaultStatus($order->getStoreId()),
                    'hash' => $this->mathRandom->getRandomString(16));
            }
            $this->connection->insertMultiple($this->resource->getTableName('swissup_reviewreminder_entity'), $ordersData);
        }
        $processed = $this->getRequest()->getParam('processed', 0) + count($orders);
        $finished  = (int)(count($orders) < $pageSize);

        return $this->getResponse()->setBody(
            $this->jsonEncoder->encode(array(
                'finished'  => $finished,
                'processed' => $processed,
                'last_processed' => $orders->getLastItem()->getId()
            ))
        );
    }
}
