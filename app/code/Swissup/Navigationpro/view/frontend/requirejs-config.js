var config = {
    map: {
        '*': {
            'navpro': 'Swissup_Navigationpro/js/navpro'
        }
    },
    config: {
        mixins: {
            'Magento_Theme/js/view/breadcrumbs': {
                'Swissup_Navigationpro/js/mixin/product-breadcrumbs': true
            }
        }
    }
};
