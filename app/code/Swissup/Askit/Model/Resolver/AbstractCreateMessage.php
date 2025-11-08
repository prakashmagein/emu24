<?php
declare(strict_types=1);

namespace Swissup\Askit\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Swissup\Askit\Model\Resolver\DataProvider\Message as DataProvider;

abstract class AbstractCreateMessage implements ResolverInterface
{
    /**
     * @var DataProvider
     */
    protected $dataProvider;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Swissup\Askit\Helper\Config
     */
    protected $configHelper;

     /**
      *
      * @var \Swissup\Askit\Model\MessageFactory
      */
    protected $messageFactory;

    /**
     *
     * @var \Swissup\Askit\Api\MessageRepositoryInterface
     */
    protected $messageRepository;

    /**
     * @var \Swissup\Askit\Model\Message\Validator
     */
    protected $validator;

    /**
     * @param DataProvider $dataProvider
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Swissup\Askit\Helper\Config $configHelper
     * @param \Swissup\Askit\Model\MessageFactory $messageFactory
     * @param \Swissup\Askit\Api\MessageRepositoryInterface $messageRepository
     * @param \Swissup\Askit\Model\Message\Validator $validator
     */
    public function __construct(
        DataProvider $dataProvider,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Swissup\Askit\Helper\Config $configHelper,
        \Swissup\Askit\Model\MessageFactory $messageFactory,
        \Swissup\Askit\Api\MessageRepositoryInterface $messageRepository,
        \Swissup\Askit\Model\Message\Validator $validator
    ) {
        $this->dataProvider = $dataProvider;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
        $this->configHelper = $configHelper;
        $this->messageFactory = $messageFactory;
        $this->messageRepository = $messageRepository;
        $this->validator = $validator;
    }

    /**
     * @param $email
     * @return \Magento\Customer\Api\Data\CustomerInterface|\Magento\Customer\Model\Customer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getCustomer($email)
    {
        $isLoggedIn = $this->customerSession->isLoggedIn();
        $customerSession = $this->customerSession->getCustomer();
        $customer = $customerSession;
        if (!$isLoggedIn) {
            try {
                $customer = $this->customerRepository->get($email);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $customer = $customerSession;
            }
        }

        return $customer;
    }

    /**
     * @param array $data
     * @return void
     * @throws GraphQlInputException
     */
    protected function validateData($data)
    {
        try {
            $this->validator->validate($data);
        } catch (\Magento\Framework\Validator\Exception $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }
    }
}
