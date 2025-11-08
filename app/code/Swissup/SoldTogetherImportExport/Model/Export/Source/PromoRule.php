<?php

namespace Swissup\SoldTogetherImportExport\Model\Export\Source;

use Magento\CatalogRule\Model\Rule\Action\SimpleActionOptionsProvider;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class PromoRule extends AbstractSource
{
    /**
     * @var SimpleActionOptionsProvider
     */
    private $rulesProvider;

    /**
     * @param SimpleActionOptionsProvider $rulesProvider
     */
    public function __construct(
        SimpleActionOptionsProvider $rulesProvider
    ) {
        $this->rulesProvider = $rulesProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllOptions()
    {
        return $this->rulesProvider->toOptionArray();
    }
}
