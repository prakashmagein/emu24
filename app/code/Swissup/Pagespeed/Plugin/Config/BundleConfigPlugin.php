<?php

namespace Swissup\Pagespeed\Plugin\Config;

class BundleConfigPlugin
{
    /**
     *
     * @param  \Magento\Deploy\Config\BundleConfig$subject
     * @param  array $result
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetExcludedDirectories(
        \Magento\Deploy\Config\BundleConfig $subject,
        $result
    ) {
        $result[] = 'Swissup_Pagespeed::js/bundle';
        $result = array_unique($result);

        return $result;
    }
}
