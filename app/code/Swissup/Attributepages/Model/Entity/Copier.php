<?php
namespace Swissup\Attributepages\Model\Entity;

class Copier
{
    /**
     * @var \Swissup\Attributepages\Model\EntityFactory
     */
    protected $entityFactory;
    /**
     * @param \Swissup\Attributepages\Model\EntityFactory $entityFactory
     */
    public function __construct(
        \Swissup\Attributepages\Model\EntityFactory $entityFactory
    ) {
        $this->entityFactory = $entityFactory;
    }
    /**
     * Create entity duplicate
     *
     * @param \Swissup\Attributepages\Model\Entity $entity
     * @return \Swissup\Attributepages\Model\Entity
     */
    public function copy(\Swissup\Attributepages\Model\Entity $entity)
    {
        $duplicate = $this->entityFactory->create()
            ->setData($entity->getData())
            ->setIsDuplicate(true)
            ->setIdentifier($entity->getIdentifier())
            ->setId(null)
            ->setStoreId($entity->getStoreId());

        $isDuplicateSaved = false;
        do {
            $identifier = $duplicate->getIdentifier();
            $identifier = preg_match('/(.*)-(\d+)$/', $identifier, $matches)
                ? $matches[1] . '-' . ($matches[2] + 1)
                : $identifier . '-1';
            $duplicate->setIdentifier($identifier);
            try {
                $duplicate->save();
                $isDuplicateSaved = true;
            } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
            }
        } while (!$isDuplicateSaved);

        return $duplicate;
    }
}
