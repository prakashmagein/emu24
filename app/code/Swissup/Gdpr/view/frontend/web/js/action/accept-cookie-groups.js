define([
    'jquery'
], function ($) {
    'use strict';

    var result = function (groups) {
        window.gdpr_updateGoogleConsent?.(groups);

        return $.ajax({
            url: window.swissupGdprCookieSettings.saveUrl,
            method: 'post',
            dataType: 'json',
            global: false,
            data: {
                form_key: $.mage.cookies.get('form_key'),
                groups: groups
            },
            success: function (data) {
                if (data.error) {
                    console.error(data.message);
                }
            }
        });
    };

    result.component = 'Swissup_Gdpr/js/action/accept-cookie-groups';

    return result;
});
