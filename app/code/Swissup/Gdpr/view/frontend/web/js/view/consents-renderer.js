define([
    'Magento_Ui/js/lib/view/utils/async',
    'underscore',
    'ko'
], function ($, _, ko) {
    'use strict';

    var result,
        methods = [
            'append',
            'prepend',
            'after',
            'before'
        ],
        template = '<% _.each(data.consents, function(consent) { %>' +
                '<div class="field required choice consent gdpr-js-consent">' +
                    '<input type="checkbox"' +
                        ' name="swissup_gdpr_consent[<%= consent.html_id %>]"' +
                        ' data-validate="{required:true}"' +
                        ' data-form-part="<%= data.formPart %>"' +
                        ' aria-required="true"' +
                        ' value="1" id="swissup_gdpr_<%= consent.html_id %>_<%= data.uniqueid %>" class="checkbox">' +
                    '<label for="swissup_gdpr_<%= consent.html_id %>_<%= data.uniqueid %>" class="label">' +
                        '<span><%= consent.title %></span>' +
                    '</label>' +
                '</div>' +
            '<% }); %>',
        templateWithoutCheckbox = '<% _.each(data.consents, function(consent) { %>' +
                ' <span class="consent gdpr-js-consent">(<%= consent.title %>)</span>' +
            '<% }); %>';

    /**
     * @param {Object} form
     */
    function initHiddenConsents(form) {
        $(form).addClass('hidden-consents');

        $(form).find('input, select, textarea').on('click focus', function () {
            $(form).addClass('visible-consents');
            $(form).removeClass('hidden-consents');
        });
    }

    function findWithFirstLastFeature(complexSelector, scope) {
        var lastParts = complexSelector.trim().split(':last'),
            lastPartsEnd = lastParts.pop(),
            res = scope;

        function findWithFirstFeature(selector) {
            var firstParts = selector.trim().split(':first'),
                firstPartsEnd = firstParts.pop();

            firstParts.forEach(sel => {
                res = res.find(sel).first();
            });

            if (firstPartsEnd) {
                res = res.find(firstPartsEnd);
            }

            return res;
        }

        lastParts.forEach(selector => {
            res = findWithFirstFeature(selector).last();
        });

        if (lastPartsEnd) {
            res = findWithFirstFeature(lastPartsEnd);
        }

        return res;
    }

    /**
     * Add consents to the form
     * @param {Object} form
     * @param {Object} config
     */
    result = function render(form, config) {
        var el, consents;

        form = $(form).closest('form');
        config = $.extend({
            checkbox: true,
            destination: '> .fieldset .field:not(.captcha):not(.g-recaptcha):not(.field-recaptcha)',
            method: 'after'
        }, config);

        if ($.breeze) {
            el = findWithFirstLastFeature(config.destination, form).last();
        } else {
            el = $(config.destination, form).last();
        }

        if (!el.length) {
            return;
        }

        if (methods.indexOf(config.method) === -1) {
            config.method = 'append';
        }

        consents = $(_.template(config.checkbox ? template : templateWithoutCheckbox)({
            data: {
                consents: config.consents,
                uniqueid: Math.floor(Math.random() * (9999 - 1000) + 1000),
                formPart: ko.dataFor(form[0])?.namespace
            }
        }));

        if (el.css('max-width')) {
            consents.css('max-width', el.css('max-width'));
        }

        el[config.method](consents);

        if (config.checkbox &&
            config.consents.length &&
            form.attr('action') &&
            form.attr('action').includes('newsletter/subscriber/new')
        ) {
            initHiddenConsents(form);
        }
    };

    result.component = 'Swissup_Gdpr/js/view/consents-renderer';

    return result;
});
