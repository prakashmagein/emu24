<?php

namespace Swissup\Ajaxpro\Plugin\Customer\CustomerData;

use Magento\Customer\CustomerData\SectionPool;

class SectionPoolPlugin
{
    /**
     * @var \Swissup\Ajaxpro\Helper\Config
     */
    private $configHelper;

    /**
     * @param \Swissup\Ajaxpro\Helper\Config $configHelper
     */
    public function __construct(
        \Swissup\Ajaxpro\Helper\Config $configHelper
    ) {
        $this->configHelper = $configHelper;
    }

    /**
     *
     * @param SectionPool $subject
     * @param array|null $sectionNames
     * @param bool $forceNewTimestamp
     * @return array
     */
    public function beforeGetSectionsData(SectionPool $subject, ?array $sectionNames = null, bool $forceNewTimestamp = false): array
    {
        if ($sectionNames !== null) {
            $ajaxproSections = [];
            $otherSections = [];

            foreach ($sectionNames as $sectionName) {
                if (strpos($sectionName, 'ajaxpro-') === 0) {
                    $ajaxproSections[] = $sectionName;
                } else {
                    $otherSections[] = $sectionName;
                }
            }
            $cartType = $this->configHelper->getCartHandle();
            $sectionNames = $cartType !== 'ajaxpro_popup_simple' ?
                array_merge($otherSections, $ajaxproSections) :
                    array_merge($ajaxproSections, $otherSections);
        }

        return [$sectionNames, $forceNewTimestamp];
    }
}
