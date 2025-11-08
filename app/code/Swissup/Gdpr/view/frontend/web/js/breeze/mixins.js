define([
    'Swissup_Gdpr/js/model/cookie-manager'
], function (cookieManager) {
    'use strict';

    if (!window.swissupGdprCookieSettings) {
        return;
    }

    $.mixin('googleAnalytics', {
        isAllowed: function () {
            return cookieManager.cookie('_ga').status();
        }
    });

    $.mixin('cookieNotices', {
        create: function (o) {
            o();
            this.element.hide();

            // if gdpr is accepted, but cookie is missing - force its creation
            if (cookieManager.group('necessary').status() && !$.cookies.get(this.options.cookieName)) {
                $(this.options.cookieAllowButtonSelector).click();
            }
        }
    });
});
