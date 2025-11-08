<?php

namespace Swissup\Gdpr\Observer;

class RegisterCookieGroups implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Prepare forms
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $observer->getCollection()
            ->addItemFromArray([
                'code' => 'necessary',
                'title' => 'Necessary',
                'description' => "Necessary cookies enable core functionality such as security, network management, and accessibility. You may disable these by changing your browser settings, but this may affect how the website functions.",
                'sort_order' => 1,
                'required' => true,
            ])
            ->addItemFromArray([
                'code' => 'preferences',
                'title' => 'Preferences',
                'description' => "Preference cookies are used to store settings and information that changes the website look and functionality.",
                'sort_order' => 5,
            ])
            ->addItemFromArray([
                'code' => 'marketing',
                'title' => 'Marketing',
                'description' => "Marketing cookies help us provide our visitors with relevant content, browsing history, and product recommendations.",
                'sort_order' => 10,
            ])
            // DEPRECATED, use marketing instead of advertisement
            ->addItemFromArray([
                'code' => 'advertisement',
                'title' => 'Advertisement',
                'description' => "Advertisement cookies help us provide our visitors with relevant ads and marketing campaigns.",
                'sort_order' => 20,
            ])
            ->addItemFromArray([
                'code' => 'analytics',
                'title' => 'Analytics',
                'description' => "Analytics cookies help us understand how our visitors interact with the website. It helps us understand the number of visitors, where the visitors are coming from, and the pages they navigate. The cookies collect this data and are reported anonymously.",
                'sort_order' => 30,
            ]);
    }
}
