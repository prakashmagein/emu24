define([
    'jquery',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'ko',
    'underscore',
    'mage/apply/main',
    'mage/apply/scripts',
    'Swissup_Ajaxpro/js/modal-manager',
    'mage/dataPost'
], function ($, Component, customerData, ko, _, mage, processScripts, ModalManager) {
    'use strict';

    var config;

    return Component.extend({
        data: {},

        /**
         *
         * @param  {Object} options
         * @returns {exports.initialize}
         */
        initialize: function (options) {
            var self = this,
            sections = ['ajaxpro-cart', 'ajaxpro-product', 'ajaxpro-reinit'];

            config = options;

            this._log('called "initialize"');
            this._log(options);
            // customerData.invalidate(sections);
            // this._log($.initNamespaceStorage('mage-cache-storage-section-invalidation').localStorage.get());
            _.each(sections, function (section) {
                var ajaxproData = customerData.get(section);

                //disposableCustomerData remove section from $.cookieStorage.get('section_data_ids') by timeout
                // so section newer expired customer-data.js getExpiredSectionNames
                ajaxproData.extend({disposableCustomerData: section});

                self.update(ajaxproData());
                ajaxproData.subscribe(self._subscribe, self);
            });
            return this._super();
        },

        /**
         *
         * @param {Mixed} data
         */
        _log: function (data) {
            if (config &&
                config.hasOwnProperty('debug')
                && config.debug
            ) {
                console.log(data);
            }
        },

        /**
         * @param {Object} updatedData
         */
        _subscribe: function (updatedData) {
            var keys;

            this._log('called "_subscribe" func');
            this._log(arguments);
            this._log(updatedData);

            this.isLoading(false);
            this.update(updatedData);
            keys = _.keys(updatedData);
            this._log(keys);

            // give some time to render js components, to decrease jumping content
            setTimeout(function () {
                $(mage.apply);
                processScripts();
                $('body').trigger('contentUpdated');

                const modalKeys = keys.filter($.proxy(ModalManager.hasKey, ModalManager));
                _.each(modalKeys, $.proxy(ModalManager.evalScripts, ModalManager));
                _.each(modalKeys, $.proxy(ModalManager.checkAndShow, ModalManager));
            }, 100);
        },
        isLoading: ko.observable(false),

        /**
         * @return {Boolean}
         */
        isActive: function () {
            return true;
        },

        /**
         * @param {Element} element
         */
        afterRender: function (element) {
            var el = $(element).closest('.block-ajaxpro');

            if (el) {
                ModalManager.register(element.id, el);
            }
        },

        /**
         * @return {String}
         */
        version: function () {
            return config.version;
        },

        /**
         * Update sections content.
         *
         * @param {Object} updatedData
         */
        update: function (updatedData) {
            this._log('called "update" func');
            this._log(updatedData);
            _.each(updatedData, function (value, key) {
                if (!this.data.hasOwnProperty(key)) {
                    this.data[key] = ko.observable();
                }
                this.data[key](value);
            }, this);
        },

        /**
         * Get ajaxpro param by name.
         * @param {String} name
         * @returns {*}
         */
        bindBlock: function (name) {
            this._log('called "bindBlock"');
            this._log(name);

            if (!_.isUndefined(name)) {
                if (!this.data.hasOwnProperty(name)) {
                    this.data[name] = ko.observable();
                }
            }

            return this.data[name]();
        }
    });
});
