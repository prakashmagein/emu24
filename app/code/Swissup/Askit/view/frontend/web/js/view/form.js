define([
    'jquery',
    'ko',
    'Magento_Ui/js/form/form',
    'Magento_Customer/js/customer-data',
    'uiRegistry',
    'mage/cookies'
], function ($, ko, Component, customerData, registry) {
    'use strict';

    /**
     * Initialize form_key for JS form
     */
    function _initFromKey() {
        if (!window.FORM_KEY) {
            window.FORM_KEY = $.mage.cookies.get('form_key');
        }
    }

    /**
     * Create message box.
     *
     * @param {HTMLElement} element
     */
    function _addMessageBox(element) {
        var messageBox, id;

        id = $(element).attr('id') + '-message';
        messageBox = $('<div></div>').attr('id', id);
        $(element).attr('data-errors-message-box', '#' + id);
        $(element).parent().append(messageBox);
    }

    return Component.extend({
        customer: null,
        askedSuccessfully: false,
        defaults: {
            autocomplete: 'on',
            ajaxSave: true,
            isFormVisible: false,
            formId: 'askit-question-form',
            template: null
        },

        /**
         * {@inheritdoc}
         */
        initialize: function () {
            var that = this;

            this._super();
            this._listenResponse();
            this.customer = customerData.get('customer');
            this.customerName = this.customer().fullname;

            // Figure out form visibility status
            if (window.location.hash === '#' + this.formId) {
                this.isFormVisible = true;
            }

            $.async({
                component: this,
                selector: 'input, textarea'
            }, function (element) {
                // Add 'data-form-part' attribute to inputs.
                $(element).attr('data-form-part', that.namespace);

                // Add message box for checkboxes. Validation messages don't appear on product page.
                // Reason: magento/module-catalog/view/frontend/web/product/view/validation.js
                if ($(element).is(':radio, :checkbox') &&
                    $(element).data('validate')
                ) {
                    _addMessageBox(element);
                }
            });

            this.observe('isFormVisible askedSuccessfully');
        },

        /**
         * Form save response listeners.
         */
        _listenResponse: function () {
            var that = this;

            //listen response data update
            this.responseData.subscribe(function (newData) {
                var messageContainer = that.getMessageContainer(),
                    isError = false;

                // show messages
                if (newData.messages) {
                    newData.messages.forEach(function (message) {
                        if (message.type === 'error') {
                            isError = true;
                            messageContainer.addErrorMessage({
                                'message': message.text
                            });
                        } else {
                            messageContainer.addSuccessMessage({
                                'message': message.text
                            });
                        }
                    });
                }

                // redirect page if backUrl returned.
                if (newData.backUrl) {
                    location.href = newData.backUrl;
                }

                if (!isError) {
                    that.askedSuccessfully(true);
                }
            });
        },

        /**
         * {@inheritdoc}
         */
        save: function () {
            _initFromKey();
            this._super(true, {});

            return false;
        },

        /**
         * Get form message container.
         *
         * @return {Object}
         */
        getMessageContainer: function () {
            return this.getRegion('askit-messages')()[0].messageContainer;
        },

        /**
         * {@inheritdoc}
         *
         * Fix missing source
         * For some reason data source is not always assigned.
         */
        validate: function () {
            if (!this.source) {
                this.source = registry.get(this.provider);
            }
            this._super();
        }
    });
});
