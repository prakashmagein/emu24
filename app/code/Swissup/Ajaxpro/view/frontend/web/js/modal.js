define([
    'jquery',
    'mage/template',
    'underscore',
    'Magento_Ui/js/modal/modal'
], function ($, $t, _) {
    'use strict';

    $.widget('swissup.ajaxproModal', $.mage.modal, {
        options: {
            modalClass: 'ajaxpro-modal-dialog',
            clickableOverlay: true,
            closeTimeout: 50,
            closeCounterInterval: 10
        },

        /**
         * https://github.com/magento/magento2/issues/7399
         * @return {Element} - current element.
         */
        openModal: function () {
            var result = this._super(),
            wrapper = $('.modal-inner-wrap');
            // console.log('openModel');

            this._addCloseModalIterval();
            $('.' + this.options.overlayClass).appendTo('.modal-popup._show');
            //Setting z-index of inner-wrap to 905 so that it is actually clickable and not hiding behind the overlay

            wrapper.css('z-index', 905);

            if (wrapper.css('position') === 'static') {
                wrapper.css('position', 'relative');
            }

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
            continueButtons = $('.modal-inner-wrap .modal-footer .ajaxpro-continue-button')
            ;
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
                        // clearInterval(self.interval);
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
            var self;

            if (!this.options.isOpen) {
                return this.element;
            }
            self = this;

            this._removeKeyListener();
            this.options.isOpen = false;
            this.modal.one(this.options.transitionEvent, function () {
                self._close();
            });
            this.modal.removeClass(this.options.modalVisibleClass);

            if (!this.options.transitionEvent) {
                self._close();
            }
            clearInterval(self.interval);
            _.each(['mousemove', 'click', 'scroll', 'keyup'], function (eventName) {
                eventName += '.swissupajaxproidle';
                $('body').off(eventName);
            });

            return this.element;
        },

        /**
         * Destroy overlay.
         */
        _destroyOverlay: function () {
            if (this._getVisibleCount()) {
                this.overlay.unbind().on('click', this.prevOverlayHandler);
            } else {
                $(this.options.appendTo).removeClass(this.options.parentModalClass);

                if (this.overlay != null) {
                    this.overlay.remove();
                }
                this.overlay = null;
            }
        }
    });

    return $.swissup.ajaxproModal;
});
