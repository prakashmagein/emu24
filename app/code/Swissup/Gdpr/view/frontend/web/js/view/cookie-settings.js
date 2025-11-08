define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    'mage/translate',
    'Swissup_Gdpr/js/model/cookie-manager'
], function ($, _, ko, Component, $t, cookieManager) {
    'use strict';

    return Component.extend({
        component: 'Swissup_Gdpr/js/view/cookie-settings',
        timers: {},
        messages: {},
        statuses: {},

        initialize: function () {
            this._super();

            _.each(this.groups, function (group) {
                var status;

                if (this.statuses[group.code]) {
                    return;
                }

                status = cookieManager.group(group.code).status();

                if (!status && (!cookieManager.isCookieExists() || cookieManager.group(group.code).data('isNew'))) {
                    status = cookieManager.group(group.code).data('prechecked');
                }

                this.statuses[group.code] = ko.observable(status);
            }.bind(this));
        },

        /**
         * @param {String} code
         * @param {Object} event
         */
        keypress: function (code, event) {
            if (event.key !== ' ') {
                return;
            }
            this.toggleGroup(code);
        },

        /**
         * Enable all cookie groups
         */
        enableAll: function () {
            _.each(this.groups, function (group) {
                if (this.statuses[group.code]()) {
                    return;
                }
                this.toggleGroup(group.code);
            }.bind(this));
        },

        /**
         * Enable all cookie groups and accept consents
         */
        enableAllAndSave: function (component, event) {
            this.enableAll();
            this.acceptConsent(component, event);
        },

        /**
         * @param {String} code
         */
        toggleGroup: function (code) {
            var group = cookieManager.group(code),
                status = !this.statuses[code]();

            if (group.required()) {
                return this.showMessage(code, $t('This group is required.'));
            }

            this.statuses[code](status);

            if (cookieManager.isCookieExists()) {
                group.status(status);

                setTimeout(function () {
                    this.showMessage(code, $t('Saved'));
                }.bind(this), 100);
            }
        },

        /**
         * @param {String} groupCode
         * @param {String} message
         */
        showMessage: function (groupCode, message) {
            if (this.timers[groupCode]) {
                clearTimeout(this.timers[groupCode]);
                this.timers[groupCode] = false;
            }

            this.messages[groupCode](message);

            if (message.length) {
                this.timers[groupCode] = setTimeout(function () {
                    this.messages[groupCode]('');
                }.bind(this), 1500);
            }
        },

        /**
         * @param {String} code
         * @return {Boolean}
         */
        isGroupEnabled: function (code) {
            return this.statuses[code]();
        },

        /**
         * @param {String} code
         * @return {String}
         */
        getMessage: function (code) {
            if (!this.messages[code]) {
                this.messages[code] = ko.observable('');
            }

            return this.messages[code]();
        },

        /**
         * @param {Object} component
         * @param {Object} event
         */
        acceptConsent: function (component, event) {
            _.each(this.groups, function (group) {
                cookieManager.group(group.code).status(this.statuses[group.code]());
            }.bind(this));

            $('#btn-cookie-allow').trigger('click'); // built-in cookie restriction notice

            $(event.target)
                .width($(event.target).width())
                .addClass('gdpr-loading')
                .text($t('Saving..'));

            cookieManager.updateCookie(function () {
                $(event.target)
                    .removeClass('gdpr-loading')
                    .text($t('Saved'));
            });
        },

        /**
         * Discard previously accepted consent
         */
        discardConsent: function () {
            cookieManager.removeCookie();
            window.location.reload();
        },

        /**
         * @return {Boolean}
         */
        isAcceptButtonVisible: function () {
            return cookieManager.hasNewGroups();
        }
    });
});
