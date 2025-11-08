(function () {
    'use strict';

    $.mixin('catalogAddToCart', {
        /**
         * @param {Function} original
         * @param {Object} response
         */
        getResponseData: function (original, response) {
            // disable redirect to back url when ajaxpro is recieved (modal popup)
            // @see catalog-product-view.js
            if (response.body.ajaxpro) {
                delete response.body.backUrl;
            }

            return original(response);
        }
    });
})();
