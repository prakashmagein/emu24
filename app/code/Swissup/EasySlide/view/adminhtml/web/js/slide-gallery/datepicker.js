define([
    'jquery',
    'underscore',
    'knockout',
    'moment',
    'Magento_Ui/js/form/element/date'
], function ($, _, ko, moment, Datepicker) {
    'use strict';

    return function (options, element) {
        const $timestamp = $(options.sourceSelector);
        const datepicker = new Datepicker(_.extend({
            dataScope: '',
            template: 'ui/form/components/single/field',
            tracks: {
                inputName: true,
                uid: true
            }
        }, options));

        const getFormattedValue = () => {
            if (!$timestamp.val()) return;

            return moment
                .unix($timestamp.val())
                .tz(datepicker.storeTimeZone)
                .format(datepicker.pickerDateTimeFormat)
        };

        const updateTimestamp = () => {
            const value = Math.floor(new Date(datepicker.value()) / 1000);

            $timestamp.val(value || '');
            $timestamp.trigger('change');
        }

        datepicker.shiftedValue(getFormattedValue());
        datepicker.shiftedValue.subscribe(_.debounce(updateTimestamp, 10));

        ko.renderTemplate(
            datepicker.getTemplate(),
            datepicker,
            {},
            element
        );
    }
});
