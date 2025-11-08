define([
    'jquery',
    'ko',
    'underscore',
    'mage/cookies',
    'jquery/jquery-storageapi',
    'domReady!'
], function ($, ko, _) {
    'use strict';

    var acceptedGroups = [],
        rejectedGroups = [],
        isCookieExists = ko.observable(),
        settings = window.swissupGdprCookieSettings || {},
        dummyGroup = {
            code: '!notfound',
            required: false
        },
        updateCookie,
        cookies = {},
        groups = {};

    /**
     * @param {String} code
     * @return {Object}
     */
    function getGroupSettings(code) {
        if (!settings.groups[code]) {
            return dummyGroup;
        }

        return settings.groups[code];
    }

    /**
     * Return setting from settings.cookies object.
     * If not found, try to use search algorithm:
     *
     *  dc_gtm_someid will return setting of dc_gtm_* key.
     *
     * @param {String} name
     * @return {String|Boolean}
     */
    function getCookieSettings(name) {
        var patterns;

        if (settings.cookies[name]) {
            return settings.cookies[name];
        }

        // find declared patterns
        patterns = _.filter(_.keys(settings.cookies), function (key) {
            return key.indexOf('*') > 0;
        });

        // remove * from the patterns
        patterns = _.map(patterns, function (pattern) {
            return pattern.replace('*', '');
        });

        // move longest patterns to the top
        patterns.sort(function (a, b) {
            return b.length - a.length;
        });

        // find pattern to use
        name = _.find(patterns, function (prefix) {
            return name.indexOf(prefix) === 0 && name.length > prefix.length;
        });

        if (name) {
            return settings.cookies[name + '*'];
        }

        return false;
    }

    /**
     * @param {String} name
     * @return {Object}
     */
    function getGroupSettingsByCookieName(name) {
        var cookie = getCookieSettings(name);

        if (!cookie || !settings.groups[cookie.group]) {
            console.log('Unknown Cookie: ' + name);

            require(['Swissup_Gdpr/js/action/register-unknown-cookie'], registerUnknownCookie => {
                registerUnknownCookie(name);
            });

            return dummyGroup;
        }

        return settings.groups[cookie.group];
    }

    /**
     * @param {String} code
     * @param {Boolean} status
     */
    function initGroup(code, status) {
        var group = getGroupSettings(code),
            isNew = !acceptedGroups.includes(code) && !rejectedGroups.includes(code);

        if (group.code === dummyGroup.code) {
            isNew = false;
        }

        groups[code] = {
            code: code,
            status: ko.observable(status || group.required),
            isNew: isNew,
            required: group.required,
            prechecked: group.prechecked
        };
    }

    /**
     * @param {String} name
     * @return {Object}
     */
    function getGroupByCookieName(name) {
        var config = getGroupSettingsByCookieName(name);

        if (!groups[config.code]) {
            initGroup(config.code);
        }

        return groups[config.code];
    }

    /**
     * @return {Array}
     */
    function getAllowedGroupNames(invert) {
        return _.pluck(_.filter(groups, function (group) {
            if (group.code === dummyGroup.code) {
                return false;
            }
            return invert ? !group.status() : group.status();
        }), 'code');
    }

    /**
     * @param {String} groupCode
     */
    function removeCookies(groupCode) {
        var groupCookies = _.filter(settings.cookies, function (el) {
            return el.group === groupCode && $.mage.cookies.get(el.name);
        });

        _.map(groupCookies, function (cookie) {
            // some modules use this
            $.mage.cookies.clear(cookie.name);

            // other use this (different domain here)
            if (window.cookieStorage && window.cookieStorage.setItem) {
                window.cookieStorage.setItem(cookie.name, '', {
                    expires: new Date('Jan 01 1970 00:00:01 GMT')
                });
            }
        });
    }

    /**
     * Remove module's cookie
     */
    function removeCookie() {
        $.mage.cookies.clear(settings.cookieName);
    }

    /**
     * Update cookie value
     */
    function updateCookieNow(callback) {
        var date = new Date(),
            expires = new Date(date);

        acceptedGroups = getAllowedGroupNames();
        rejectedGroups = getAllowedGroupNames(true);
        _.map(groups, group => {
            group.isNew = false;
        });

        expires.setDate(expires.getDate() + settings.lifetime);

        $.mage.cookies.set(
            settings.cookieName,
            JSON.stringify({
                groups: acceptedGroups,
                rejected: rejectedGroups,
                date: date.getTime()
            }),
            {
                expires: expires
            }
        );

        isCookieExists(true);

        require(['Swissup_Gdpr/js/action/accept-cookie-groups'], acceptCookieGroups => {
            acceptCookieGroups(acceptedGroups).then(callback);
        });
    }

    updateCookie = _.debounce(updateCookieNow, 200);

    /**
     * Read cookie
     */
    (function readCookie() {
        var consent = $.mage.cookies.get(settings.cookieName);

        if (!consent || !consent.length) {
            return;
        }

        try {
            consent = JSON.parse(consent);
            acceptedGroups = consent.groups || [];
            rejectedGroups = consent.rejected || [];
        } catch (e) {
            return;
        }

        isCookieExists(true);

        _.each(consent.groups, function (name) {
            initGroup(name, true);
        });
    })();

    /**
     * Init all declared groups
     */
    (function initGroups() {
        _.each(settings.groups, function (group) {
            if (!groups[group.code]) {
                initGroup(group.code);
            }
        });
    })();

    /**
     * Allow all cookie groups
     */
    function allowAllGroups() {
        _.map(groups, function (group) {
            if (group.code === '!notfound') {
                return;
            }

            return group.status(true);
        });

        updateCookie();
    }

    $(document).on('click', '[data-cookies-allow-all]', function () {
        allowAllGroups();
    });

    $(document).on('click', '[data-cookies-allow-necessary]', function () {
        groups.necessary.status(true);
        updateCookie();
    });

    $(document).on('click', '[data-cookies-settings]', function (e) {
        window.location = $(e.target).data('href');
    });

    $(document).on('click', '[data-cookies-accept]', function () {
        updateCookie();
    });

    return {
        component: 'Swissup_Gdpr/js/model/cookie-manager',

        /**
         * @return {Boolean}
         */
        isCookieExists: isCookieExists,

        /**
         * Update cookie value
         */
        updateCookie: updateCookie,

        /**
         * Remove cookie
         */
        removeCookie: removeCookie,

        groups: () => groups,

        hasNewGroups: function () {
            return _.some(groups, group => group.isNew);
        },

        /**
         * @param {String} name
         * @return {Object}
         */
        group: function (name) {
            if (!groups[name]) {
                initGroup(name);
            }

            return {
                /**
                 * @param {Boolean} flag
                 * @return {Mixed}
                 */
                status: function (flag) {
                    if (flag !== undefined) {
                        if (!flag) {
                            removeCookies(name);
                        }

                        groups[name].status(flag);

                        if (!isCookieExists()) {
                            return; // client must press 'Accept' button to save cookie
                        }

                        return updateCookie();
                    }

                    return groups[name].status();
                },

                /**
                 * @return {Boolean}
                 */
                required: function () {
                    return groups[name].required;
                },

                /**
                 * @param {String} key
                 * @return {Mixed}
                 */
                data: function (key) {
                    return groups[name][key];
                }
            };
        },

        /**
         * @param {String} name
         * @return {Object}
         */
        cookie: function (name) {
            if (!cookies[name]) {
                cookies[name] = {
                    name: name,
                    status: function () {
                        return getGroupByCookieName(name).status();
                    }
                };
            }

            return {
                /**
                 * @return {Boolean}
                 */
                status: function () {
                    return cookies[name].status();
                }
            };
        }
    };
});
