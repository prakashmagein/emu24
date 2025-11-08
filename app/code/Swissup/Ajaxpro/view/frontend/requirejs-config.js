var config = {
    map: {
        '*': {
            catalogAddToCart: 'Swissup_Ajaxpro/js/catalog-add-to-cart',
            compareItems: 'Swissup_Ajaxpro/js/compare'
        }
    },
    config: {
        mixins: {
            'Magento_ConfigurableProduct/js/configurable': {
                'Swissup_Ajaxpro/js/mixin/configurable': true
            }
        }
    }
};
