<?php
declare(strict_types=1);

namespace Swissup\Askit\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Swissup\Askit\Api\Data\MessageInterface;
use Swissup\Askit\Model\Resolver\DataProvider\Message as DataProvider;

class CreateQuestion extends AbstractCreateMessage implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        if (!$this->configHelper->isEnabled()) {
            throw new GraphQlInputException(__('Extension is temporary disabled'));
        }

        if (empty($args['product_id']) || !is_int($args['product_id'])) {
            throw new GraphQlInputException(__('"product_id" value should be specified'));
        }

        if (empty($args['email']) || !is_string($args['email'])) {
            throw new GraphQlInputException(__('"email" value should be specified'));
        }

        if (empty($args['customer_name']) || !is_string($args['customer_name'])) {
            throw new GraphQlInputException(__('"customer_name" value should be specified'));
        }

        if (empty($args['text']) || !is_string($args['text'])) {
            throw new GraphQlInputException(__('"text" value should be specified'));
        }

        $postData = [
            'item_id' => (int) $args['product_id'],
            'item_type_id' => MessageInterface::TYPE_CATALOG_PRODUCT,
            'customer_name' => (string) $args['customer_name'],
            'email' => (string) $args['email'],
            'text' => (string) $args['text'],
        ];

        $this->validateData($postData);

        $message = $this->execute($postData);

        $data = $this->dataProvider
            ->setMessage($message)
            ->getData();

        return $data;
    }

    /**
     * @param array $postData
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function execute(array $postData)
    {
        $customer = $this->getCustomer($postData['email']);

        $postData['customer_id'] = $customer->getId() ? $customer->getId() : null;
        $postData['customer_name'] = $customer->getId() ?
            $customer->getFirstName() . ' ' . $customer->getLastName() : $postData['customer_name'];
        $postData['email'] = $customer->getId() ? $customer->getEmail() : $postData['email'];
        $postData['store_id'] = $this->storeManager->getStore()->getId();

        $postData['status'] = $this->configHelper->getDefaultQuestionStatus();
        $postData['hint'] = 0;

        $message = $this->messageFactory->create();
        $message->setData($postData);
        $this->messageRepository->save($message);

        return $message;
    }
}
