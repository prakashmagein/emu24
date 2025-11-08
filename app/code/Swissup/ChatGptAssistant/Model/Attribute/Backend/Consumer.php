<?php
namespace Swissup\ChatGptAssistant\Model\Attribute\Backend;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\TemporaryStateExceptionInterface;
use Magento\Framework\Bulk\OperationInterface;

class Consumer
{
    private \Psr\Log\LoggerInterface $logger;

    private \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor;

    private \Magento\Catalog\Model\Product\Action $productAction;

    private \Magento\Framework\Serialize\SerializerInterface $serializer;

    private \Magento\Framework\EntityManager\EntityManager $entityManager;

    private \Swissup\ChatGptAssistant\Model\ResourceModel\Prompt\CollectionFactory $promptCollectionFactory;

    private \Swissup\ChatGptAssistant\Model\Filter\Product $productFilter;

    private \Magento\Catalog\Api\ProductRepositoryInterface $productRepository;

    private \Swissup\ChatGptAssistant\Model\ChatGptRequestFactory $requestFactory;

    public function __construct(
        \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor,
        \Magento\Catalog\Model\Product\Action $action,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\EntityManager\EntityManager $entityManager,
        \Swissup\ChatGptAssistant\Model\ResourceModel\Prompt\CollectionFactory $promptCollectionFactory,
        \Swissup\ChatGptAssistant\Model\Filter\Product $productFilter,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Swissup\ChatGptAssistant\Model\ChatGptRequestFactory $requestFactory
    ) {
        $this->productFlatIndexerProcessor = $productFlatIndexerProcessor;
        $this->productAction = $action;
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->promptCollectionFactory = $promptCollectionFactory;
        $this->productFilter = $productFilter;
        $this->productRepository = $productRepository;
        $this->requestFactory = $requestFactory;
    }

    /**
     * @param \Magento\AsynchronousOperations\Api\Data\OperationInterface $operation
     * @throws \Exception
     *
     * @return void
     */
    public function process(\Magento\AsynchronousOperations\Api\Data\OperationInterface $operation)
    {
        try {
            $serializedData = $operation->getSerializedData();
            $data = $this->serializer->unserialize($serializedData);
            $this->execute($data);
        } catch (\Zend_Db_Adapter_Exception $e) {
            $this->logger->critical($e->getMessage());
            if ($e instanceof \Magento\Framework\DB\Adapter\LockWaitException
                || $e instanceof \Magento\Framework\DB\Adapter\DeadlockException
                || $e instanceof \Magento\Framework\DB\Adapter\ConnectionException
            ) {
                $status = OperationInterface::STATUS_TYPE_RETRIABLY_FAILED;
                $errorCode = $e->getCode();
                $message = $e->getMessage();
            } else {
                $status = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
                $errorCode = $e->getCode();
                $message = __(
                    'Sorry, something went wrong during product attributes update. Please see log for details.'
                );
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->critical($e->getMessage());
            $status = ($e instanceof TemporaryStateExceptionInterface)
                ? OperationInterface::STATUS_TYPE_RETRIABLY_FAILED
                : OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            $errorCode = $e->getCode();
            $message = $e->getMessage();
        } catch (LocalizedException $e) {
            $this->logger->critical($e->getMessage());
            $status = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            $errorCode = $e->getCode();
            $message = $e->getMessage();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $status = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            $errorCode = $e->getCode();
            $message = __('Sorry, something went wrong during product attributes update. Please see log for details.');
        }

        $operation->setStatus($status ?? OperationInterface::STATUS_TYPE_COMPLETE)
            ->setErrorCode($errorCode ?? null)
            ->setResultMessage($message ?? null);

        $this->entityManager->save($operation);
    }

    /**
     * @param array $data
     * @return void
     */
    private function execute($data): void
    {
        $attributes = $data['attributes'];
        $productIds = $data['product_ids'];
        $storeId = $data['store_id'];

        $prompts = $this->promptCollectionFactory->create()
            ->addFieldToFilter('entity_id', ['in' => $attributes]);

        foreach ($productIds as $productId) {
            $attributesContent = [];
            foreach ($attributes as $code => $promptId) {
                $prompt = $prompts->getItemById($promptId);
                if (!$prompt) {
                    throw new NoSuchEntityException(
                        __("The prompt that was requested doesn't exist. Verify the prompt and try again.")
                    );
                }
                $promptText = $prompt->getText();
                $product = $this->productRepository->getById($productId);
                $product->setStoreId($storeId);
                $attributeCode = str_replace('prompt_', '', $code);

                $filteredPromptText = $this->productFilter
                    ->setScope($product)
                    ->filter($promptText);

                $generatedContent = $this->requestFactory->create()->sendRequest($filteredPromptText);

                if ($generatedContent['success']) {
                    $attributesContent[$attributeCode] = $generatedContent['result'];
                } else {
                    throw new LocalizedException(__('Error generating content: %1', $generatedContent['result']));
                }
            }

            if ($attributesContent) {
                $this->productAction->updateAttributes([$productId], $attributesContent, $storeId);
            }
        }

        $this->productFlatIndexerProcessor->reindexList($productIds);
    }
}
