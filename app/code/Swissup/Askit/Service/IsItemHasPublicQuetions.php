<?php
namespace Swissup\Askit\Service;

use Magento\Store\Model\Store;

class IsItemHasPublicQuetions
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Swissup\Askit\Api\MessageRepositoryInterface
     */
    private $messageRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Swissup\Askit\Model\Message\Source\PublicStatuses
     */
    private $publicStatuses;

    /**
     * @param \Swissup\Askit\Api\MessageRepositoryInterface $messageRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Swissup\Askit\Model\Message\Source\PublicStatuses $publicStatuses
     */
    public function __construct(
        \Swissup\Askit\Api\MessageRepositoryInterface $messageRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Swissup\Askit\Model\Message\Source\PublicStatuses $publicStatuses
    ) {
        $this->messageRepository = $messageRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->publicStatuses = $publicStatuses;
    }

    /**
     * @param $itemId
     * @param $itemTypeId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function has($itemId, $itemTypeId)
    {
        $openStatuses = $this->publicStatuses->getOptionArray();
        $openStatuses = array_keys($openStatuses);
        $storeId = $this->storeManager->getStore()->getId();

        $criteria = $this->searchCriteriaBuilder
            ->addFilter('parent_id', 0)
            ->addFilter('status', $openStatuses, 'in')
            ->addFilter('item_id', $itemId)
            ->addFilter('item_type_id', $itemTypeId)
            ->addFilter('store_id', [$storeId, Store::DEFAULT_STORE_ID],'in')
            ->create();
        $list = $this->messageRepository->getList($criteria);

        return $list->getTotalCount() > 0;
    }
}
