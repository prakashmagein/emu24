define([
    'jquery',
    'knockout',
    'Magento_Ui/js/form/element/single-checkbox',
    'mage/translate'
], function ($, ko, Checkbox, $t) {
    'use strict';

    return function (options, element) {
        const toggler = new Checkbox({
            dataScope: '',
            prefer: 'toggle',
            tracks: {
                inputName: true,
                uid: true
            },
            toggleLabels: {
                'on': $t('Visible at storefront'),
                'off': $t('Hidden at storefront')
            },
            valueMap: {
                true: '1',
                false: '0'
            },
            value: options.value
        });

        ko.renderTemplate(
            toggler.getTemplate(),
            toggler,
            {},
            element
        );

        // set input name
        if (typeof options.inputName !== 'undefined')
            toggler.inputName = options.inputName;
        // set input ID
        if (typeof options.uid !== 'undefined')
            toggler.uid = options.uid;

    }
});
