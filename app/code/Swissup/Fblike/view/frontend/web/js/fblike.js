(function (factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            './fbinit',
            'Magento_Ui/js/modal/modal' // 2.3.3: create 'jquery-ui-modules/widget' dependency
        ], factory);
    } else {
        if (typeof FB !== 'undefined' && FB.swissupReady) {
            factory($, FB);
        } else {
            $(document).on('fblike:ready', function (event, data) {
                factory($, data.fb);
            });
        }
    }
}(function ($, FB) {
    'use strict';

    $.widget('swissup.fblike', {
        component: 'Swissup_Fblike/js/fblike',

        /**
         * [_create description]
         */
        _create: function () {
            FB.swissupReady($.proxy(this.fbInit, this));
        },

        destroy: function () {
            this.element.removeClass('fbl-ready');
            this._super();
        },

        /**
         * Bind events
         */
        _bind: function () {
            if (!this.element.hasClass('fbl-ready')) {
                this.element.addClass('fbl-ready');
                this._on({
                    'click .like': this._triggerFbLike
                });
            }
        },

        /**
         * Initialize facebook button
         */
        fbInit: function () {
            FB.XFBML.parse(this.element.get(0));
            FB.swissupFBInit();
            this._bind();
        },

        /**
         * @param  {Event} event
         */
        _triggerFbLike: function (event) {
            // call fb dialog to like product
            // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
            window.FB.ui(
                {
                    method: 'share_open_graph',
                    action_type: 'og.likes',
                    action_properties: JSON.stringify({
                        object: $(event.currentTarget).data('url')
                    })
                },
                function (response) {} // eslint-disable-line no-unused-vars
            );
            FB.swissupFBInit();
            // jscs:enable requireCamelCaseOrUpperCaseIdentifiers
        }
    });

    return $.swissup.fblike;
}));
