var config = {
    config: {
        mixins: {
            'Magento_Cookie/js/notices': {
                'Swissup_Gdpr/js/mixin/cookie-notice-mixin': true
            },
            'Magento_GoogleAnalytics/js/google-analytics': {
                'Swissup_Gdpr/js/mixin/google-analytics-mixin': true
            }
        }
    },
    deps: [
        'Swissup_Gdpr/js/model/cookie-blocker'
    ]
};
