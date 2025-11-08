define([
    'Magento_Ui/js/lib/view/utils/async',
    'underscore',
    'mage/template',
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    'text!Swissup_ChatGptAssistant/template/ai-button.html',
    'Swissup_ChatGptAssistant/js/chat'
], function ($, _, mageTemplate, $t, alert, aiButtonTmpl, chatWidget) {
    'use strict';

    var self, url, fields;

    return {
        init: function(config) {
            self = this;
            url = config.url;
            fields = config.fields;

            this.addButtons();
        },

        addButtons: function() {
            _.each(fields, function(field) {
                if ($(field.async.ctx).length) {
                    $.async({
                        selector: field.async.selector,
                        ctx: $(field.async.ctx).get(0)
                    }, function (el) {
                        const targetField = $(el),
                            aiButton = self.createButton(field, targetField),
                            chatPopup = chatWidget({ parentClass: self, prompts: field.prompts, targetField: targetField });

                        self.addButtonListeners(aiButton, chatPopup, field, targetField);
                    });
                }
            });
        },

        /**
         * @param {Object} field field configuration
         * @param {Element} $el target field
         * @returns {Element}
         */
        createButton: function(field, $el) {
            const buttonId = 'ai_button_' + $.guid++,
                button = $(mageTemplate(aiButtonTmpl, {
                    generateText: $t('Generate'),
                    generateTitle: $t('Generate content using the default prompt.'),
                    moreTitle: $t('Start a chat...'),
                    htmlId: buttonId
                }));

            if (field.style) {
                button.css(field.style);
            }
            $el.after(button).parent().css('position', 'relative');

            return button;
        },

        /**
         * @param {Element} button
         * @param {Element} popup
         * @param {Object} field
         * @param {Element} targetField
         */
        addButtonListeners: function(button, popup, field, targetField) {
            const defaultPrompt = _.findWhere(
                field.prompts, {default_field_id: field.id}
            );

            button.children('.action-default').on('click', function() {
                if (defaultPrompt) {
                    self.makeRequest(defaultPrompt.text, button, targetField);
                } else {
                    popup.showPopup();
                }
            });
            button.children('.action-toggle').on('click', function() {
                popup.showPopup();
            });
        },

        /**
         * @param {String} prompt
         * @param {Element} button
         * @param {Element} targetField
         */
        makeRequest: function(prompt, button, targetField) {
            button.addClass('loading');
            targetField.prop('disabled', true);

            self.request(prompt)
            .done(function(data) {
                self.setFieldContent(targetField, data.result);
            })
            .always(function() {
                button.removeClass('loading');
                targetField.prop('disabled', false);
            });
        },

        /**
         * @param {Element} field
         * @param {String} content
         */
        setFieldContent: function(field, content) {
            field.val(content);
            field.trigger('change');// for PageBuilder

            // update wysiwyg
            if (tinyMCE) {
                const editor = tinyMCE.get(field.attr('id'));
                editor && editor.setContent(content);
            }
        },

        /**
         * @param {Array|String} prompt
         * @returns {jqXHR}
         */
        request: function(prompt) {
            const request = $.ajax({
                url: url,
                method: 'POST',
                dataType: 'json',
                data: {
                    form_key: window.FORM_KEY,
                    prompt: prompt
                }
            });

            request.fail(function(jqXHR) {
                alert({
                    title: $t('Error'),
                    content: jqXHR.responseJSON.result
                });
            })

            return request;
        }
    }
});
