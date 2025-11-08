define([
    'jquery'
], function ($) {
    'use strict';

    var options = {};

    return {
        component: 'Swissup_Ajaxpro/js/loader',
        setLoaderImage: function () {
            return this;
        },
        setLoaderImageMaxWidth: (width) => {
            options.loaderImageMaxWidth = width;
            return this;
        },
        startLoader: function (el) {
            el.spinner(true, {
                css: {
                    width: options.loaderImageMaxWidth,
                    height: options.loaderImageMaxWidth,
                    background: 'transparent'
                }
            });
        },
        stopLoader: (el) => el.spinner(false)
    }
});
