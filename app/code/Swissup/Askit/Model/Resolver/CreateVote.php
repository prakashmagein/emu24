<?php
declare(strict_types=1);

namespace Swissup\Askit\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Swissup\Askit\Api\Data\MessageInterface;
use Swissup\Askit\Model\Resolver\DataProvider\Message as DataProvider;

class CreateVote extends AbstractCreateMessage implements ResolverInterface
{
    private \Swissup\Askit\Model\VoteFactory $messageVoteFactory;

    /**
     * @param DataProvider $dataProvider
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Swissup\Askit\Helper\Config $configHelper
     * @param \Swissup\Askit\Model\MessageFactory $messageFactory
     * @param \Swissup\Askit\Api\MessageRepositoryInterface $messageRepository
     * @param \Swissup\Askit\Model\Message\Validator $validator
     * @param \Swissup\Askit\Model\VoteFactory $messageVoteFactory
     */
    public function __construct(
        DataProvider $dataProvider,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Swissup\Askit\Helper\Config $configHelper,
        \Swissup\Askit\Model\MessageFactory $messageFactory,
        \Swissup\Askit\Api\MessageRepositoryInterface $messageRepository,
        \Swissup\Askit\Model\Message\Validator $validator,
        \Swissup\Askit\Model\VoteFactory $messageVoteFactory
    ) {
        parent::__construct(
            $dataProvider,
            $customerSession,
            $customerRepository,
            $storeManager,
            $configHelper,
            $messageFactory,
            $messageRepository,
            $validator
        );
        $this->messageVoteFactory = $messageVoteFactory;
    }

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

        if (empty($args[MessageInterface::ID])) {
            throw new GraphQlInputException(__('"id" value should be specified'));
        }

        if (empty($args['email']) || !is_string($args['email'])) {
            throw new GraphQlInputException(__('"email" value should be specified'));
        }

        if (!isset($args['is_like']) || !is_bool($args['is_like'])) {
            throw new GraphQlInputException(__('"is_like" value should be specified'));
        }

        if ($this->validator->validateEmail($args['email']) !== true) {
            throw new GraphQlInputException(__('"email" value is invalid'));
        }

        $postData = [
            MessageInterface::ID => (int) $args[MessageInterface::ID],
            'email' => (string) $args['email'],
            'is_like' => (bool) $args['is_like'],
        ];

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
        $id = $postData[MessageInterface::ID];
        $customer = $this->getCustomer($postData['email']);
        $customerId = $customer->getId() ? $customer->getId() : null;

        $voteModel = $this->messageVoteFactory->create();
        if ($voteModel->isVoted($id, $customerId)) {
            throw new GraphQlInputException(__('Sorry, already voted'));
        }

        /** @var MessageInterface $message */
        $message = $this->messageRepository->getById($id);
        if (!$message->getId()) {
            throw new GraphQlInputException(__('"id" is incorect'));
        }

        $isLike = $postData['is_like'];
        $step = $isLike ? 1 : -1;
        $message->setHint($message->getHint() + $step);
        $message->save();

        $voteModel->setData([
            'message_id' => $message->getId(),
            'customer_id' => $customerId
        ])->save();

        return $message;
    }
}
