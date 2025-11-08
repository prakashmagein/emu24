<?php

namespace Swissup\Gdpr\Model\Config\Source;

class CookieGroup implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Swissup\Gdpr\Model\CookieGroupRepository
     */
    private $repository;

    /**
     * @param \Swissup\Gdpr\Model\CookieGroupRepository $repository
     */
    public function __construct(
        \Swissup\Gdpr\Model\CookieGroupRepository $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $groups = $this->repository->getList();
        $options = [];
        foreach ($groups as $group) {
            $options[] = [
                'label' => $group->getTitle(),
                'value' => $group->getCode(),
            ];
        }
        return $options;
    }
}
