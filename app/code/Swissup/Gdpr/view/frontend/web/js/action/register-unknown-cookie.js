define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    var result,
        names = [],
        namesSent = [],
        registerUnknownCookies;

    registerUnknownCookies = _.debounce(function () {
        return $.ajax({
            url: window.swissupGdprCookieSettings.registerUrl,
            method: 'post',
            dataType: 'json',
            global: false,
            data: {
                form_key: $.mage.cookies.get('form_key'),
                name: _.uniq(names),
                location: window.location.href
            },
            success: function (data) {
                namesSent = _.uniq(namesSent.concat(names));
                names = [];

                if (data.error) {
                    console.error(data.message);
                }
            }
        });
    }, 500);

    result = function (name) {
        if (namesSent.indexOf(name) !== -1) {
            return;
        }

        names.push(name);

        return registerUnknownCookies();
    };

    result.component = 'Swissup_Gdpr/js/action/register-unknown-cookie';

    return result;
});
