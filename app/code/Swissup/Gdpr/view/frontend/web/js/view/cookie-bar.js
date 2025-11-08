define([
    'jquery',
    'Swissup_Gdpr/js/model/cookie-manager'
], function ($, cookieManager) {
    'use strict';

    var result = function (settings, el) {
        if (!cookieManager.hasNewGroups() || $(el).hasClass('shown')) {
            return;
        }

        $(el).css('display', '').addClass('shown');
        setTimeout(() => { // wait for cookie bar animation
            if (['fixed', 'absolute'].includes($(el).css('position'))) {
                $(el).focusTrap?.(true, {
                    clickOutsideDeactivates: true,
                    returnFocusOnDeactivate: false
                });
                $(el).find('[data-cookies-allow-all]').focus();
            }
        }, 200);

        $(document.body).on('click', '.accept-cookie-consent', function () {
            $('#btn-cookie-allow').trigger('click'); // built-in cookie restriction notice
            $(el).removeClass('shown').focusTrap?.(false);
        });
    };

    $(document).on('breeze:mount:Swissup_Gdpr/js/view/cookie-bar', (e, data) => {
        result(data.settings, data.el);
    });

    return result;
});
