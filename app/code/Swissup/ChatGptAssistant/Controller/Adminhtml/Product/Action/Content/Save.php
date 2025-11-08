<?php
namespace Swissup\ChatGptAssistant\Controller\Adminhtml\Product\Action\Content;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Swissup\ChatGptAssistant\Controller\Adminhtml\Product\Action\Attribute as AttributeAction;

class Save extends AttributeAction implements HttpPostActionInterface
{
    private \Magento\Framework\Bulk\BulkManagementInterface $bulkManagement;

    private \Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory $operationFactory;

    private \Magento\Framework\DataObject\IdentityGeneratorInterface $identityService;

    private \Magento\Framework\Serialize\SerializerInterface $serializer;

    private \Magento\Authorization\Model\UserContextInterface $userContext;

    private int $bulkSize;

    public function __construct(
        Action\Context $context,
        \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper,
        \Magento\Framework\Bulk\BulkManagementInterface $bulkManagement,
        \Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory $operationFactory,
        \Magento\Framework\DataObject\IdentityGeneratorInterface $identityService,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        int $bulkSize = 20
    ) {
        parent::__construct($context, $attributeHelper);
        $this->bulkManagement = $bulkManagement;
        $this->operationFactory = $operationFactory;
        $this->identityService = $identityService;
        $this->serializer = $serializer;
        $this->userContext = $userContext;
        $this->bulkSize = $bulkSize;
    }

    /**
     * Update product attributes
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        if (!$this->_validateProducts()) {
            return $this->resultRedirectFactory->create()->setPath('catalog/product/', ['_current' => true]);
        }

        /* Collect Data */
        $attributesData = $this->getRequest()->getParam('attributes', []);
        $storeId = $this->attributeHelper->getSelectedStoreId();
        $websiteId = $this->attributeHelper->getStoreWebsiteId($storeId);
        $productIds = $this->attributeHelper->getProductIds();

        try {
            $this->publish($attributesData, $storeId, $websiteId, $productIds);
            $this->messageManager->addSuccessMessage(__('Message is added to queue'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while updating the product(s) attributes.')
            );
        }

        return $this->resultRedirectFactory->create()->setPath('catalog/product/', ['store' => $storeId]);
    }

    /**
     * Schedule new bulk
     *
     * @param array $attributesData
     * @param int $storeId
     * @param int $websiteId
     * @param array $productIds
     * @throws LocalizedException
     *
     * @return void
     */
    private function publish(
        $attributesData,
        $storeId,
        $websiteId,
        $productIds
    ):void {
        $productIdsChunks = array_chunk($productIds, $this->bulkSize);
        $bulkUuid = $this->identityService->generateId();
        $bulkDescription = __('Generate content with AI for ' . count($productIds) . ' selected products');
        $operations = [];
        foreach ($productIdsChunks as $productIdsChunk) {
            if ($attributesData) {
                $operations[] = $this->makeOperation(
                    'Generate content with AI',
                    'swissup.chatGptAssistant.productContent.generate',
                    $attributesData,
                    $storeId,
                    $websiteId,
                    $productIdsChunk,
                    $bulkUuid
                );
            }
        }

        if (!empty($operations)) {
            $result = $this->bulkManagement->scheduleBulk(
                $bulkUuid,
                $operations,
                $bulkDescription,
                $this->userContext->getUserId()
            );
            if (!$result) {
                throw new LocalizedException(
                    __('Something went wrong while processing the request.')
                );
            }
        }
    }

    /**
     * Make asynchronous operation
     *
     * @param string $meta
     * @param string $queue
     * @param array $dataToUpdate
     * @param int $storeId
     * @param int $websiteId
     * @param array $productIds
     * @param int $bulkUuid
     *
     * @return OperationInterface
     */
    private function makeOperation(
        $meta,
        $queue,
        $dataToUpdate,
        $storeId,
        $websiteId,
        $productIds,
        $bulkUuid
    ): OperationInterface {
        $dataToEncode = [
            'meta_information' => $meta,
            'product_ids' => $productIds,
            'store_id' => $storeId,
            'website_id' => $websiteId,
            'attributes' => $dataToUpdate
        ];
        $data = [
            'data' => [
                'bulk_uuid' => $bulkUuid,
                'topic_name' => $queue,
                'serialized_data' => $this->serializer->serialize($dataToEncode),
                'status' => \Magento\Framework\Bulk\OperationInterface::STATUS_TYPE_OPEN,
            ]
        ];

        return $this->operationFactory->create($data);
    }
}
