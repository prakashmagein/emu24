define([], function () {
    'use strict';

    var options = false;

    return {
        component: 'Swissup_Ajaxpro/js/get-product-view-request',
        options: {},
        'Swissup_Ajaxpro/js/get-product-view-request': function (settings) {
            if (options === false) {
                options = settings;
            }
        },
        get: function (productId) {
            var sectionNames = ['ajaxpro-product'],
                parameters = {
                    sections: sectionNames.join(','),
                    'update_section_id': false,
                    ajaxpro: {
                        'product_id': productId,
                        blocks: ['product.view']
                    }
                },
                url = options.sectionLoadUrl,
                me = this;

            parameters[options.refererParam] = options.refererValue;
            parameters[options.refererQueryParamName] = options.refererValue;

            return {
                // method: 'POST',
                url: url,
                dataType: 'json',
                data: parameters
            };
        }
    }
});
