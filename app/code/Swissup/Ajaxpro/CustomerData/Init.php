<?php

namespace Swissup\Ajaxpro\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Swissup\Ajaxpro\CustomerData\AbstractSectionData;

class Init extends AbstractSectionData implements SectionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $return = [
            'reinit' => $this->getBlockHtml('ajaxpro.init', ['default'])
        ];
        $this->flushLayouts();

        return $return;
    }
}
