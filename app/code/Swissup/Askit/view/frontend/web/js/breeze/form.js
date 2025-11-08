define([
    'ko',
    'Magento_Ui/js/view/messages',
    'Magento_Ui/js/form/form'
], (ko, Messages, Form) => {
    'use strict';

    Messages.extend({
        component: 'Swissup_Askit/js/view/messages'
    });

    Form.extend({
        component: 'Swissup_Askit/js/view/form',
        defaults: {
            customer: $.customerData.get('customer'),
            askedSuccessfully: false,
            isFormVisible: false,
            autocomplete: 'on',
            ajaxSave: true,
            formId: 'askit-question-form',
            template: null
        },

        /** [create description] */
        create: function () {
            this.customerName = this.customer().fullname;
            this.askedSuccessfully = ko.observable(this.askedSuccessfully);
            this.isFormVisible = ko.observable(this.isFormVisible);

            if (window.location.hash === '#' + this.formId) {
                this.isFormVisible(true);
            }
        },

        /** [viewReady description] */
        afterRender: function () {
            this.messageContainer = this.getRegion('askit-messages')()[0];
        },

        /** [save description] */
        save: function () {
            var self = this,
                result = this._super();

            self.messageContainer.removeAll();

            if (!result || !result.then) {
                return;
            }

            self.toggleLoader(true);

            result.then(function (response) {
                var error = false,
                    data = response?.body || {};

                if (data.backUrl) {
                    location.href = data.backUrl;

                    return;
                }

                self.toggleLoader(false);

                if (data.messages) {
                    data.messages.forEach(function (message) {
                        var method = 'addSuccessMessage';

                        if (message.type === 'error') {
                            error = true;
                            method = 'addErrorMessage';
                        }

                        self.messageContainer[method](message.text);
                    });
                }

                if (!error) {
                    $('#question_text').val('');
                    self.askedSuccessfully(true);
                }
            });
        },

        toggleLoader: function (flag) {
            this.element.find('button[type="submit"]')
                .prop('disabled', flag)
                .spinner(flag);
        }
    });
});
