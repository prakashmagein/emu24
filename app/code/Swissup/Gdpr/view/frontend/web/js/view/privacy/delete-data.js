(function (factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define(['jquery', 'mage/translate', 'Magento_Ui/js/modal/modal'], factory);
    } else {
        $(document).on('breeze:mount:Swissup_Gdpr/js/view/privacy/delete-data', function (event, data) {
            factory($, $.__)(data.settings, data.el);
        });
    }
}(function ($, $t) {
    'use strict';

    var config;

    /**
     * Create popup
     */
    function createConfirmationPopup() {
        $(config.popup).first().modal({
            'type': 'popup',
            'modalClass': 'delete-data-modal',
            'responsive': true,
            'innerScroll': true,
            'trigger': '.show-modal',
            'buttons': [
                {
                    text: $t('Yes, Delete My Data'),
                    class: 'action delete-data',

                    /** @inheritdoc */
                    click: function () {
                        var form = $(config.popup).find('form');

                        if (form.validation().valid()) {
                            form.closest('.delete-data-modal')
                                .find('.delete-data')
                                .prop('disabled', true);

                            form.submit();
                        }
                    }
                }
            ]
        });
    }

    /**
     * Show popup
     */
    function showConfirmationPopup() {
        $(config.popup).first().modal('openModal');
    }

    return function (data, el) {
        config = data;

        createConfirmationPopup();

        $(el).on('click', showConfirmationPopup);
    };
}));
