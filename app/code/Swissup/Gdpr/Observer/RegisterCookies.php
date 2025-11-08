<?php

namespace Swissup\Gdpr\Observer;

class RegisterCookies implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Swissup\Gdpr\Helper\Data
     */
    private $helper;

    public function __construct(
        \Swissup\Gdpr\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Prepare forms
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $observer->getCollection()
            // Necessary
            ->addItemFromArray([
                'name' => 'PHPSESSID',
                'description' => "Preserves the visitor's session state across page requests.",
                'sort_order' => -10,
                'group' => 'necessary',
            ])
            ->addItemFromArray([
                'name' => 'form_key',
                'description' => "Protects visitor's data from Cross-Site Request Forgery Attacks.",
                'group' => 'necessary',
            ])
            ->addItemFromArray([
                'name' => 'guest-view',
                'description' => "Stores the Order ID that guest shoppers use to retrieve their order status. Used in “Orders and Returns” widgets.",
                'group' => 'necessary',
            ])
            ->addItemFromArray([
                'name' => 'login_redirect',
                'description' => "Preserves the destination page the customer was loading before being directed to log in.",
                'group' => 'necessary',
            ])
            ->addItemFromArray([
                'name' => 'mage-banners-cache-storage',
                'description' => "Stores banner content locally to improve performance.",
                'group' => 'necessary',
            ])
            ->addItemFromArray([
                'name' => 'mage-cache-sessid',
                'description' => "The value of this cookie triggers the cleanup of local cache storage.",
                'group' => 'necessary',
            ])
            ->addItemFromArray([
                'name' => 'mage-cache-storage',
                'description' => "Local storage of visitor-specific content that enables ecommerce functions.",
                'group' => 'necessary',
            ])
            ->addItemFromArray([
                'name' => 'mage-cache-storage-section-invalidation',
                'description' => "Forces local storage of specific content sections that should be invalidated.",
                'group' => 'necessary',
            ])
            ->addItemFromArray([
                'name' => 'mage-messages',
                'description' => "Tracks error messages and other notifications that are shown to the user, such as the cookie consent message, and various error messages. The message is deleted from the cookie after it is shown to the shopper.",
                'group' => 'necessary',
            ])
            ->addItemFromArray([
                'name' => 'persistent_shopping_cart',
                'description' => "Stores the key (ID) of persistent cart to make it possible to restore the cart for an anonymous shopper.",
                'group' => 'necessary',
                'status' => $this->helper->getConfigValue('persistent/options/enabled'),
            ])
            ->addItemFromArray([
                'name' => 'private_content_version',
                'description' => "Appends a random, unique number and time to pages with customer content to prevent them from being cached on the server.",
                'group' => 'necessary',
            ])
            ->addItemFromArray([
                'name' => 'section_data_ids',
                'description' => "Stores customer-specific information related to shopper-initiated actions such as display wish list, checkout information, etc.",
                'group' => 'necessary',
            ])
            ->addItemFromArray([
                'name' => 'user_allowed_save_cookie,' . \Swissup\Gdpr\Model\CookieManager::COOKIE_NAME,
                'description' => "Stores the user's cookie consent state for the current domain.",
                'group' => 'necessary',
            ])
            ->addItemFromArray([
                'name' => 'X-Magento-Vary',
                'description' => "Configuration setting that improves performance when using Varnish static content caching.",
                'group' => 'necessary',
            ])

            // Preferences
            ->addItemFromArray([
                'name' => 'store',
                'description' => "Remembers the user's selected language version of a website.",
                'group' => 'preferences',
            ])

            // Marketing
            ->addItemFromArray([
                'name' => 'stf',
                'description' => "Records the time messages are sent by the SendFriend (Email a Friend) module.",
                'group' => 'marketing',
                'status' => $this->helper->getConfigValue('sendfriend/email/enabled'),
            ])
            ->addItemFromArray([
                'name' => '_fbp',
                'description' => "This cookie is installed by Facebook to store and track visits across websites.",
                'group' => 'marketing',
            ])
            ->addItemFromArray([
                'name' => 'product_data_storage',
                'description' => "Stores configuration for product data related to Recently Viewed / Compared Products.",
                'group' => 'marketing',
            ])
            ->addItemFromArray([
                'name' => 'recently_compared_product',
                'description' => "Stores product IDs of recently compared products.",
                'group' => 'marketing',
            ])
            ->addItemFromArray([
                'name' => 'recently_compared_product_previous',
                'description' => "Stores product IDs of previously compared products for easy navigation.",
                'group' => 'marketing',
            ])
            ->addItemFromArray([
                'name' => 'recently_viewed_product',
                'description' => "Stores product IDs of recently viewed products for easy navigation.",
                'group' => 'marketing',
            ])
            ->addItemFromArray([
                'name' => 'recently_viewed_product_previous',
                'description' => "Stores product IDs of recently previously viewed products for easy navigation.",
                'group' => 'marketing',
            ])
            ->addItemFromArray([
                'name' => '_gac_*',
                'description' => "Contains campaign-related information for the user. Google AdWords conversion tags read this cookie if Google Analytics is linked to your AdWords account.",
                'group' => 'marketing',
                'status' => $this->helper->getConfigValue('google/adwords/active'),
            ])

            // Analytics
            ->addItemFromArray([
                'name' => '_ga,_gat,_gid,_ga_*,_gat_*',
                'description' => "This cookie is installed by Google Analytics. They are used to collect information about how visitors use our website. We use the information to compile reports and to help us improve the website. The cookies collect information in a way that does not directly identify anyone.",
                'group' => 'analytics',
            ])
            ->addItemFromArray([
                'name' => 'dc_gtm_*',
                'description' => "Throttles request rate when Google Analytics is deployed with Google Tag Manager.",
                'group' => 'analytics',
            ])
            ->addItemFromArray([
                'name' => 'add_to_cart,remove_from_cart',
                'description' => "Used by Google Tag Manager. Captures the product SKU, name, price and quantity added or removed from the cart, and makes the information available for future integration by third-party scripts.",
                'group' => 'analytics',
                'status' => $this->helper->getConfigValue('google/analytics/active'),
            ]);
    }
}
