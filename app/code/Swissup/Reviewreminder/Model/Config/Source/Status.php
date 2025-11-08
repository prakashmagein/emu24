<?php
namespace Swissup\Reviewreminder\Model\Config\Source;

class Status implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Swissup\Reviewreminder\Model\Entity
     */
    protected $entity;
    /**
     * Constructor
     *
     * @param \Swissup\Reviewreminder\Model\Entity $entity
     */
    public function __construct(\Swissup\Reviewreminder\Model\Entity $entity)
    {
        $this->entity = $entity;
    }
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->entity->getAvailableStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
