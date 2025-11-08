define([
    'Magento_Captcha/js/view/checkout/defaultCaptcha',
    'Magento_Captcha/js/model/captcha',
    'Magento_Captcha/js/model/captchaList',
    'Magento_Ui/js/lib/view/utils/async',
    'underscore',
    'uiRegistry'
],
function (defaultCaptcha, Captcha, captchaList, $, _, registry) {
    'use strict';

    return defaultCaptcha.extend({
        /** @inheritdoc */
        initialize: function () {
            var captcha, that = this;

            this._super();
            this.configSource.formId = this.formId;
            captcha = Captcha(this.configSource);
            captchaList.add(captcha);

            if (captcha != null) {
                captcha.setIsVisible(true);
                this.setCurrentCaptcha(captcha);
            }

            // rename captcha input.
            $.async({
                component: this,
                selector: 'input[name=captcha_string]'
            }, function (element) {
                $(element).attr('name', 'captcha[' + that.formId + ']');
                $(element).removeAttr('id');
                // Add 'data-form-part' attribute to inputs.
                $(element).attr('data-form-part', that.parentName);
            });

            // listen form response from submit and refresh recaptcha
            registry.async(this.parentName)(function (form) {
                form.responseData?.subscribe(function () {
                    _.defer(that.refresh.bind(that));
                });
            });
        }
    });
});
