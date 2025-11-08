<?php
declare(strict_types=1);

namespace Swissup\Askit\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Swissup\Askit\Model\Resolver\DataProvider\Message as DataProvider;

class CreateAnswer extends AbstractCreateMessage implements ResolverInterface
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

        if (empty($args['parent_id']) || !is_int($args['parent_id'])) {
            throw new GraphQlInputException(__('"parent_id" value should be specified'));
        }

        if (empty($args['text']) || !is_string($args['text'])) {
            throw new GraphQlInputException(__('"text" value should be specified'));
        }

        $postData = [
            'parent_id' => (int) $args['parent_id'],
            'email' => (string) $args['email'],
            'customer_name' => (string) $args['customer_name'],
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
     * @throws GraphQlInputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function execute(array $postData)
    {
        $customer = $this->getCustomer($postData['email']);

        /** @var \Swissup\Askit\Model\Message $question */
        $question = $this->messageRepository->getById($postData['parent_id']);

        if (!$question->getId()) {
            throw new GraphQlInputException(__('"parent_id" must be valid'));
        }

        $postData['item_id'] = $question->getItemId();
        $postData['item_type_id'] = $question->getItemTypeId();
        $postData['customer_id'] = $customer->getId() ? $customer->getId() : null;
        $postData['customer_name'] = $customer->getId() ?
            $customer->getFirstName() . ' ' . $customer->getLastName() : $postData['customer_name'];
        $postData['email'] = $customer->getId() ? $customer->getEmail() : $postData['email'];

        $postData['store_id'] = $this->storeManager->getStore()->getId();

        $postData['status'] = $this->configHelper->getDefaultAnswerStatus();
        $postData['hint'] = 0;

        $message = $this->messageFactory->create();
        $message->setData($postData);
        $this->messageRepository->save($message);

        return $message;
    }
}
