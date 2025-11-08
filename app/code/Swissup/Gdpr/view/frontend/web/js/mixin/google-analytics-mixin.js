define([
    'mage/utils/wrapper',
    'Swissup_Gdpr/js/model/cookie-manager'
], function (wrapper, cookieManager) {
    'use strict';

    return function (target) {
        if (!window.swissupGdprCookieSettings) {
            return target;
        }

        return wrapper.wrap(
            target,
            function (o, config) {
                if (!cookieManager.cookie('_ga').status()) {
                    return;
                }

                config.isCookieRestrictionModeEnabled = false;

                o(config);
            }
        );
    };
});
