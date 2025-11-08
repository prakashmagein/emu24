var config = {
    config: {
        mixins: {
            'Magento_Search/form-mini': {
                'Swissup_Ajaxsearch/js/form-mini-mixin': true
            },
            'Magento_Search/js/form-mini': {
                'Swissup_Ajaxsearch/js/form-mini-mixin': true
            }
        }
    },
    shim: {
        'Swissup_Ajaxsearch/js/lib/select2.min': {
            deps: ['jquery']
        }
    }
};
