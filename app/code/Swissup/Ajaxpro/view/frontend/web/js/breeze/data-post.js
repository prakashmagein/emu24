(function () {
    'use strict';

    var ajaxcianSelectors = [],
        customer = $.customerData.get('customer');

    /**
     * @param {Object} form
     * @return {Boolean}
     */
    function canProcess(form) {
        if (form.attr('action').indexOf('/wishlist/') > -1 && !customer().fullname) {
            return false;
        }

        if (!form.target) {
            return false;
        }

        return form.target.is(ajaxcianSelectors.join(','));
    }

    $.mixin('dataPost', {
        /**
         * @param {Function} original
         * @param {Object} form
         */
        submitForm: function (original, form) {
            if (!canProcess(form)) {
                return original(form);
            }

            form.target.css('color', 'transparent').spinner(true, {
                css: {
                    width: 20,
                    height: 20
                }
            });

            $.request.post({
                form: form
            }).then(function (response) {
                var data = {};

                if (response && response.body) {
                    data = response.body;
                }

                // give some time to update related sections and then hide spinner
                setTimeout(function () {
                    form.target.css('color', '').spinner(false);
                }, 200);

                if (data && data.backUrl) {
                    if (data.backUrl.indexOf('customer/section/load/') !== -1) {
                        data.backUrl = form.attr('action');
                    }
                    window.location = data.backUrl;
                }
            });
        }
    });

    // collect all selectors supported by ajaxpro
    $.widget('ajaxcianDataPost', {
        component: 'Swissup_Ajaxpro/js/ajaxcian-data-post',

        /** [create description] */
        create: function () {
            if (this.options.__selector &&
                ajaxcianSelectors.indexOf(this.options.__selector) === -1
            ) {
                ajaxcianSelectors.push(this.options.__selector);
            }
        }
    });
})();
