<?php
namespace Swissup\Reviewreminder\Helper;

use Swissup\Reviewreminder\Model\Entity as ReminderModel;
use Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory;
use Magento\Catalog\Model\Config\Source\Product\Thumbnail;
use Magento\ConfigurableProduct\Model\Product\Configuration\Item\ItemProductResolver;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Helper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var CollectionFactory
     */
    protected $historyCollectionFactory;
    /**
     *
     * @var \Swissup\Reviewreminder\Model\EntityFactory
     */
    protected $reminderFactory;
    /**
     * @var Swissup\Reviewreminder\Helper\Config
     */
    protected $configHelper;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $appEmulation;
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;
    /**
     * @var \Magento\Email\Model\TemplateFactory
     */
    protected $emailFactory;
    /**
     * @var Boolean manual email sending flag
     */
    private $isManualSend;
    /**
     * @var \Magento\GroupedProduct\Model\Product\Type\Grouped
     */
    protected $groupedType;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param CollectionFactory $historyCollectionFactory
     * @param \Swissup\Reviewreminder\Helper\Config $configHelper
     * @param \Swissup\Reviewreminder\Model\EntityFactory $reminderFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Store\Model\App\Emulation $appEmulation
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Email\Model\TemplateFactory $emailFactory
     * @param \Magento\GroupedProduct\Model\Product\Type\Grouped $groupedType
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        CollectionFactory $historyCollectionFactory,
        \Swissup\Reviewreminder\Helper\Config $configHelper,
        \Swissup\Reviewreminder\Model\EntityFactory $reminderFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Email\Model\TemplateFactory $emailFactory,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $groupedType
    ) {
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->configHelper = $configHelper;
        $this->reminderFactory = $reminderFactory;
        $this->date = $date;
        $this->orderFactory = $orderFactory;
        $this->productRepository = $productRepository;
        $this->appEmulation = $appEmulation;
        $this->imageHelper = $imageHelper;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->emailFactory = $emailFactory;
        $this->groupedType = $groupedType;
        parent::__construct($context);
    }
    /**
     * Get order created date or status change date depending from configuration
     * @param \Magento\Sales\Model\Order $order order instance
     * @return date order date
     */
    public function getOrderDate($order, $configHelper)
    {
        $orderHistoryCollection = $this->historyCollectionFactory->create()
            ->addAttributeToSelect('created_at')
            ->addAttributeToSort('created_at', 'ASC')
            ->addAttributeToFilter('parent_id', ['eq' => $order->getId()]);
        if ($configHelper->allowSpecificStatuses()) {
            $orderHistoryCollection->addAttributeToFilter('status',
                ['in' => $configHelper->specificOrderStatuses()])
            ->load();
            $orderDate = $orderHistoryCollection->getLastItem()->getCreatedAt();
            if (is_null($orderDate)) {
                $orderDate = $order->getUpdatedAt();
            }
        } else {
            $orderDate = $order->getCreatedAt();
        }
        return $orderDate;
    }
    /**
     * Send review reminders
     * @param array|null $reminderIds array of ids when sent manually or null for cron
     */
    public function sendReminders($reminderIds = null)
    {
        $this->isManualSend = ($reminderIds != null);
        $reminderModel = $this->reminderFactory->create();
        $entityCollection = $reminderModel->getCollection()->clear();
        if ($this->isManualSend) {
            $entityCollection->addFieldToFilter('entity_id', ['in' => $reminderIds]);
        } else {
            $entityCollection->addFieldToFilter('status', ['eq' => ReminderModel::STATUS_NEW]);
        }
        // check if enough days passed to send reminder
        if (!$this->isManualSend) {
            $daysAfter = $this->configHelper->getSendEmailAfter();
            if (is_int($daysAfter) && $daysAfter > 0) {
                $checkDate = date("Y-m-d H:i:s", $this->date->timestamp(time() - $daysAfter * 24 * 60 * 60));
                $entityCollection->addFieldToFilter('order_date', ['lteq' => $checkDate]);
            }
        }
        $entityCollection->getSelect()
            ->reset('columns')
            ->columns([
                'entity_ids' => 'GROUP_CONCAT(entity_id SEPARATOR ",")',
                'order_ids' => 'GROUP_CONCAT(order_id SEPARATOR ",")',
                'hashes' => 'GROUP_CONCAT(hash SEPARATOR ",")',
                'customer_email'
            ])
            ->group('customer_email');
        if (!$this->isManualSend) {
            $entityCollection->getSelect()->limit($this->configHelper->getNumOfEmailsPerCron());
        }
        foreach ($entityCollection as $entity) {
            try {
                $this->processOrders($entity);
                $this->changeOrdersStatus($reminderModel, $entity->getEntityIds(), ReminderModel::STATUS_SENT);
            } catch (\Exception $e) {
                $this->changeOrdersStatus($reminderModel, $entity->getEntityIds(), ReminderModel::STATUS_FAILED);
                throw new \Exception($e->getMessage());
            }
        }
    }

    /**
     * @param  ReminderModel $model
     * @param  string $entityIds
     * @param  int $status
     * @return void
     */
    private function changeOrdersStatus($model, $entityIds, $status)
    {
        if (strpos($entityIds, ',') === false) {
            $this->saveEntityStatus($model, $entityIds, $status);
        } else {
            $entityIdsArr = explode(',', $entityIds);
            foreach ($entityIdsArr as $entityId) {
                $this->saveEntityStatus($model, $entityId, $status);
            }
        }
    }

    /**
     * Save record status
     * @param ReminderModel $model
     * @param int $entityId
     * @param int $status
     * @return void
     */
    private function saveEntityStatus($model, $entityId, $status)
    {
        $model->load($entityId)->setStatus($status);
        try {
            $model->save();
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
    }

    /**
     * Go through orders and send emails
     * @param ReminderModel $entity
     */
    private function processOrders($entity)
    {
        $customerEmail = $entity->getCustomerEmail();
        $orderIds = $entity->getOrderIds();

        $reminderIds = explode(',', $entity->getEntityIds());
        $reminderHashes = explode(',', $entity->getHashes());
        $orderDataArr = [
            'reminder_id' => end($reminderIds),
            'reminder_hash' => end($reminderHashes),
            'customer_email' => $customerEmail
        ];

        if (strpos($orderIds, ',') === false) {
            $this->collectOrderData($orderIds, $orderDataArr);
        } else {
            $orderIdsArr = explode(',', $orderIds);
            foreach ($orderIdsArr as $orderId) {
                $this->collectOrderData($orderId, $orderDataArr);
            }
        }

        if (isset($orderDataArr[0])) {
            $this->processEmail($customerEmail, $orderDataArr);
        }
    }

    /**
     * @param  \Magento\Sales\Model\Order\Item $item
     * @return \Magento\Catalog\Model\Product|null
     */
    private function getProduct(\Magento\Sales\Model\Order\Item $item)
    {
        $product = $item->getProduct();
        if ($product && !$product->isVisibleInSiteVisibility() &&
            $parentsIds = $this->groupedType->getParentIdsByChild($product->getId()))
        {
            return $this->productRepository->getById($parentsIds[0]);
        }

        return $product;
    }

    /**
     * @param  \Magento\Sales\Model\Order\Item $item
     * @return string
     */
    private function getProductImageUrl(\Magento\Sales\Model\Order\Item $item)
    {
        $product = $this->getProduct($item);
        if ($item->getProductType() === 'configurable') {
            // Use child product for image when ordered configurable
            $configValue = $this->scopeConfig->getValue(
                ItemProductResolver::CONFIG_THUMBNAIL_SOURCE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $childProduct = $this->productRepository->get(
                $item->getProductOptions()['simple_sku']
            );
            $childThumb = $childProduct->getData('thumbnail');
            if ($configValue !== Thumbnail::OPTION_USE_PARENT_IMAGE
                && $childThumb !== null
                && $childThumb !== 'no_selection'
            ) {
                $product = $childProduct;
            }
        }

        return $this->imageHelper
            ->init($product, 'image')
            ->setImageFile($product->getImage())
            ->resize(100)
            ->getUrl();
    }

    /**
     * Collect order data by order id
     * @param  int $orderId
     * @param  array &$orderDataArr Reference to data array
     */
    public function collectOrderData($orderId, &$orderDataArr)
    {
        $order = $this->orderFactory->create()->load($orderId);
        $orderDataArr['customer_name'] = $order->getCustomerName();
        $orderDataArr['store_id'] = $order->getStoreId();
        $orderDataArr['order_id'] = $order->getIncrementId();
        $orderedItems = $order->getAllVisibleItems();

        //emulate frontend to get correct product image
        $this->appEmulation->startEnvironmentEmulation($orderDataArr['store_id']);
        foreach ($orderedItems as $item) {
            $product = $this->getProduct($item);
            if (!$product) {
                continue;
            }
            $productImageUrl = $this->getProductImageUrl($item);
            $product->setStoreId($orderDataArr['store_id']);

            $productUrl = $product->getUrlInStore([
                '_scope' => $orderDataArr['store_id'],
                '_store' => $orderDataArr['store_id'],
                '_store_to_url' => true,
                '_nosid' => true
            ]);
            array_push($orderDataArr, [
                'id' => $product->getId(),
                'url' => $productUrl,
                'name' => $product->getName(),
                'image' => $productImageUrl
            ]);
        }
        $this->appEmulation->stopEnvironmentEmulation();
    }
    /**
     * Get reminder email subject and fill variables
     * @return String email subject
     */
    public function filterEmailSubject($customerName, $productName, $storeId)
    {
        $subject = $this->configHelper->getEmailSubject($storeId);
        $subject = str_replace("{customer_name}", $customerName, $subject);
        $subject = str_replace("{product_name}", $productName, $subject);
        return $subject;
    }
    /**
     * Get list of products links for email
     * @return String
     */
    public function getProductsList($data)
    {
        $products = '';
        foreach ($data as $product) {
            if ($product['url'] && $product['name']) {
                $products .= "<a href='" . $product['url'] . "'>" . $product['name'] . "</a>, ";
            }
        }
        return $products;
    }

    /**
     * Prepare email variables and options
     * @param string $customerEmail
     * @param array $emailData Reminder email data
     * @param bool $preview if true return email template else send email
     */
    private function processEmail($customerEmail, $emailData, $preview = false)
    {
        $storeId = $emailData['store_id'];
        $trustedshopsLink = $this->configHelper->getTrustedShopsLink(
            $emailData['customer_email'], $emailData['order_id'], $storeId
        );
        $unsubscribeLink = $this->_getUrl(
            'reviewreminder/email/unsubscribe',
            [
                '_scope' => $storeId,
                'id' => $emailData['reminder_id'],
                'hash' => $emailData['reminder_hash']
            ]
        );

        $customerName = $emailData['customer_name'];
        $productName = $emailData[0]['name'];
        $subject = $this->filterEmailSubject($customerName, $productName, $storeId);

        // remove all except products
        $emailData = array_filter($emailData, function($arr) { return is_array($arr); });
        // remove duplicated products
        $emailData = array_map("unserialize", array_unique(array_map("serialize", $emailData)));
        $productsList = $this->getProductsList($emailData);

        $vars = [
            'subject' => $subject,
            'products' => $emailData,
            'customer_name' => $customerName,
            'products_list' => $productsList,
            'trustedshops_link' => $trustedshopsLink,
            'unsubscribe_link' => $unsubscribeLink
        ];
        $templateId = $this->configHelper->getEmailTemplate($storeId);
        $from = $this->configHelper->getEmailSendFrom($storeId);
        $to = [
            'email' => $customerEmail,
            'name' => $customerName
        ];

        return $preview ? $this->_previewEmail($templateId, $vars, $storeId) :
            $this->_sendEmail($from, $to, $templateId, $vars, $storeId);
    }
    /**
     * Send email to customer
     * @param  String $from             send email from
     * @param  String $to               send email to
     * @param  String|int $templateId   email template identifier
     * @param  Array $vars              email template variables
     * @param  int $storeId             order store id
     * @param  String $area             email area
     * @return Bool                     true
     */
    private function _sendEmail($from, $to, $templateId, $vars, $storeId, $area = \Magento\Framework\App\Area::AREA_FRONTEND)
    {
        if (!$this->isManualSend && !$this->configHelper->isEnabled($storeId)) {
            return $this;
        }

        $this->inlineTranslation->suspend();
        $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateOptions([
                'area' => $area,
                'store' => $storeId
            ])
            ->setTemplateVars($vars)
            ->setFromByScope($from, $storeId)
            ->addTo($to['email'], $to['name']);
        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();
        return true;
    }
    /**
     * Generate email template preview
     * @param  String|int $templateId   email template identifier
     * @param  Array $vars              email template variables
     * @param  int $storeId             order store id
     * @param  String $area             email area
     * @return String                   generated email template html
     */
    private function _previewEmail($templateId, $vars, $storeId, $area = \Magento\Framework\App\Area::AREA_FRONTEND)
    {
        /** @var $template \Magento\Email\Model\Template */
        $template = $this->emailFactory->create();
        $template->setTemplateText($template->getTemplateText());
        $template->setVars($vars);
        $template->setId($templateId);
        $template->setOptions([
            'area' => $area,
            'store' => $storeId
        ]);
        $template->load($templateId);
        $template->emulateDesign($storeId);
        $this->appEmulation->startEnvironmentEmulation($storeId);
        $templateProcessed = $template->processTemplate();
        $this->appEmulation->stopEnvironmentEmulation();
        $template->revertDesign();
        return $templateProcessed;
    }

    /**
     * Get email template preview
     * @param  ReminderModel $model reminder model
     * @return string generated email template html
     */
    public function getEmailPreviewHtml($model)
    {
        $orderDataArr = [
            'reminder_id' => $model->getId(),
            'reminder_hash' => $model->getHash(),
            'customer_email' => $model->getCustomerEmail()
        ];
        $this->collectOrderData($model->getOrderId(), $orderDataArr);

        return $this->processEmail($model->getCustomerEmail(), $orderDataArr, true);
    }
}
