<?php

namespace Swissup\Gdpr\Model\PersonalDataHandler;

use Swissup\Gdpr\Model\ClientRequest;
use Magento\Sales\Model\Order;
use Magento\Framework\Exception\LocalizedException;

class Sales extends AbstractHandler implements HandlerInterface
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory
     */
    private $addressCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory
     */
    private $creditmemoCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory
     */
    private $invoiceCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory
     */
    private $shipmentCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory
     */
    private $paymentCollectionFactory;

    /**
     * @param \Swissup\Gdpr\Model\PersonalDataHandler\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory $addressCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $creditmemoCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory $paymentCollectionFactory
     */
    public function __construct(
        \Swissup\Gdpr\Model\PersonalDataHandler\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory $addressCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $creditmemoCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory $paymentCollectionFactory
    ) {
        parent::__construct($context);
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->addressCollectionFactory = $addressCollectionFactory;
        $this->creditmemoCollectionFactory = $creditmemoCollectionFactory;
        $this->invoiceCollectionFactory = $invoiceCollectionFactory;
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->paymentCollectionFactory = $paymentCollectionFactory;
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function beforeDelete(ClientRequest $request)
    {
        $collection = $this->getOrderCollection($request)
            ->addFieldToFilter('state', ['nin' => [
                Order::STATE_COMPLETE,
                Order::STATE_CLOSED,
                Order::STATE_CANCELED,
            ]]);

        if ($collection->getSize()) {
            throw new LocalizedException(
                __(
                    "Can't remove data. Incompleted orders found: %1",
                    implode(', ', $collection->getColumnValues('increment_id'))
                )
            );
        }
    }

    /**
     * @return void
     */
    public function delete(ClientRequest $request)
    {
        $this->anonymize($request);
    }

    /**
     * @return void
     */
    public function anonymize(ClientRequest $request)
    {
        $orders = $this->getOrderCollection($request);

        if ($size = $orders->getSize()) {
            $orderIds = $orders->getColumnValues('entity_id');
            $collections = [
                $this->addressCollectionFactory->create()->addFieldToFilter('parent_id', $orderIds),
                $this->paymentCollectionFactory->create()->addFieldToFilter('parent_id', $orderIds),
                $this->creditmemoCollectionFactory->create()->addFieldToFilter('order_id', $orderIds),
                $this->invoiceCollectionFactory->create()->addFieldToFilter('order_id', $orderIds),
                $this->shipmentCollectionFactory->create()->addFieldToFilter('order_id', $orderIds),
                $orders,
            ];

            $this->anonymizeCollections(
                $collections,
                array_merge(
                    $this->faker->getAddressData($request),
                    $this->faker->getOrderData($request),
                    $this->faker->getPaymentData($request)
                )
            );
        }

        $request->addSuccess(sprintf(
            'Sales data anonymization finished. %s orders were processed.',
            $size
        ));
    }

    /**
     * @return array
     */
    public function export(ClientRequest $request)
    {
        return [];
    }

    /**
     * @param  ClientRequest $request
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    private function getOrderCollection(ClientRequest $request)
    {
        $columns = ['customer_email'];
        $values = [$request->getClientIdentity()];
        if ($request->getCustomerId()) {
            $columns[] = 'customer_id';
            $values[] = $request->getCustomerId();
        }

        $collection = $this->orderCollectionFactory->create()
            ->addFieldToFilter($columns, $values);

        if ($this->useWebsiteFilter()) {
            $collection->addFieldToFilter(
                'store_id',
                [
                    'or' => [
                        ['in' => $this->getStoreIds($request)],
                        ['null' => true],
                    ]
                ]
            );
        }

        return $collection;
    }
}
