(function () {
    'use strict';

    $.widget('ajaxproModal', 'modal', {
        component: 'Swissup_Ajaxpro/js/modal',

        options: {
            modalClass: 'ajaxpro-modal-dialog',
            clickableOverlay: true,
            closeTimeout: 50,
            closeCounterInterval: 10
        },

        /**
         * @return {Element} - current element.
         */
        openModal: function () {
            var result = this._super();

            this._addCloseModalIterval();

            return result;
        },

        /**
         * Add close interval for close modal
         */
        _addCloseModalIterval: function () {
            var self = this,
                _inteval,
                counter,
                text,
                replaceText,
                continueButtons = $('.modal-inner-wrap .modal-footer .ajaxpro-continue-button');

            if (continueButtons.length &&
                !!this.options.closeTimeout &&
                !!this.options.closeCounterInterval
            ) {
                this.modal.data('intervalCounter', this.options.closeTimeout);
                _inteval = this.options.closeCounterInterval;

                clearInterval(self.interval);
                self.interval = setInterval(function () {
                    counter = self.modal.data('intervalCounter');

                    if (counter <= _inteval) {
                        continueButtons.each(function (i, button) {
                            text = $(button).text();

                            if (-1 === text.indexOf('(')) {
                                text += ' (0)';
                            }
                            replaceText = counter <= 0 ? '' : '(' + counter + ')';

                            text = text.replace(/\(\d+\)/, replaceText);
                            $(button).text(text);
                        });
                    }

                    if (counter <= 0) {
                        self.closeModal();
                    }
                    counter--;
                    self.modal.data('intervalCounter', counter);
                }, 1000);
                _.each(['mousemove', 'click', 'scroll', 'keyup'], function (eventName) {
                    eventName += '.swissupajaxproidle';
                    $('body').on(eventName, _.bind(self._resetCloseinterval, self));
                });
            } else if (this.options.closeTimeout) {
                this._setCloseTimeout();
                _.each(['mousemove', 'click', 'scroll', 'keyup'], function (eventName) {
                    eventName += '.swissupajaxproidle';
                    $('body').on(eventName, _.bind(self._setCloseTimeout, self));
                });
            }
        },

        /**
         * Reset close interval
         */
        _resetCloseinterval: function () {
            this.modal.data('intervalCounter', this.options.closeTimeout);
        },

        /**
         * Set timeout for close modal
         */
        _setCloseTimeout: function () {
            var timeout = this.options.closeTimeout * 1000;

            clearTimeout(this.modal.data('closeTimeout'));
            this.modal.data('closeTimeout', setTimeout(this.closeModal, timeout));
        },

        /**
         * Close modal.
         * @return {Element} - current element.
         */
        closeModal: function () {
            var self = this;

            if (!this.options.isOpen) {
                return this.element;
            }

            this._super();

            clearInterval(self.interval);
            _.each(['mousemove', 'click', 'scroll', 'keyup'], function (eventName) {
                eventName += '.swissupajaxproidle';
                $('body').off(eventName);
            });

            return this.element;
        }
    });
})();
