define([
    'jquery',
    'Swissup_Ajaxpro/js/is-ajaxpro-request'
], function ($, isAjaxproRequest) {
    'use strict';

    return {
        component: 'Swissup_Ajaxpro/js/modal-manager',
        elements: {},

        destroy: function () {
            this.elements = {};
        },

        /**
         * data-bind="afterRender: afterRender
         *
         * @param {String} id
         * @param {Element} element
         */
        register: function (id, element) {
            return new Promise(resolve => {
                const interval = setInterval(() => {
                    if (!$.active) {
                        this.elements[id] = element;
                        clearInterval(interval);
                        resolve();
                    }
                }, 100);
            });
        },

        unregister: function (id) {
            this.elements[id] = false;
        },

        has: function (id) {
            return !!this.elements[id];
        },

        hasKey: function (key) {
            var id = this.getId(key);
            return !!this.elements[id];
        },

        getId: function(key) {
            return 'ajaxpro-' + key;
        },

        getElement: function(id) {
            return this.elements[id];
        },

        /**
         * Show window
         *
         * @param {String} key
         */
        show: function (key) {
            var id = this.getId(key);
            if (this.has(id)) {
                const element = this.getElement(id);
                const dialog = element.closest('.ajaxpro-modal-dialog');
                const hasPopupMessages = dialog.hasClass('ajaxpro-popup-simple') ?
                    dialog.find('.messages .message').length > 0 : true;

                if (!dialog.hasClass('_show') && hasPopupMessages) {
                    this.hide();
                    element.trigger('openModal');
                }
            }
        },

        /**
         * Show window
         *
         * @param {String} key
         */
        checkAndShow: function (key) {
            if (this.hasKey(key) && isAjaxproRequest.active()) {
                isAjaxproRequest.reset();
                this.show(key);
            }
        },

        /**
         * eval native additional js
         *
         * @param {String} key
         */
        evalScripts: function (key) {
            var id = this.getId(key);

            if (this.has(id)) {
                const element = this.getElement(id);

                $(element).find('script').filter(function (i, script) {
                    return !script.type;
                }).each(function (i, script) {
                    script = $(script).html();

                    if (script.indexOf('document.write(') !== -1) {
                        const errorMessage = 'document.write writes to the document stream, ' +
                            'calling document.write on a closed (loaded) ' +
                            'document automatically calls document.open, ' +
                            'which will clear the document.';
                        return console.error(errorMessage);
                    }

                    try {
                        return $.globalEval(script);
                    } catch (err) {
                        console.log(script);
                        console.error(err);
                    }
                });
            }
        },

        /**
         * Hide modal window
         */
        hide: function () {
            $('.block-ajaxpro').each(function (i, el) {
                $(el).trigger('closeModal');
            });
        }
    };
});
