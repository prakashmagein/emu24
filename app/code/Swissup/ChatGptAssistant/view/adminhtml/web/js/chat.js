define([
    'jquery',
    'underscore',
    'mage/template',
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    'text!Swissup_ChatGptAssistant/template/ai-popup.html',
    'text!Swissup_ChatGptAssistant/template/chat-entry.html'
], function ($, _, mageTemplate, $t, modal, aiPopupTmpl, chatEntryTmpl) {
    'use strict';

    $.widget('swissup.assistantPopup', {
        chatHistory: [],
        prompts: {},
        targetField: {},
        parentClass: {},
        popup: {},

        _create: function() {
            this.chatHistory = [];
            this.prompts = this.options.prompts;
            this.targetField = this.options.targetField;
            this.parentClass = this.options.parentClass;
            this.popup = this.initPopup();
        },

        initPopup: function() {
            const self = this,
                promptsHtml = _.map(self.prompts, function(el) {
                    return '<option value="' + el.entity_id + '">' + el.name + '</option>';
                }).join('');

            const popup = $(mageTemplate(aiPopupTmpl, {
                selectPromptText: $t('Select Prompt...'),
                chatPlaceholder: $t('Send a request to start chatting...'),
                promptPlaceholder: $t('Enter your text or select one of the predefined prompts from the above dropdown...'),
                sendTitle: $t('Send'),
                applyTitle: $t('Apply'),
                popupHtmlId: 'popup_' + $.guid++,
                prompts: promptsHtml
            }));
            modal({
                type: 'popup',
                title: $t('Custom Prompt'),
                modalClass: 'ai-generate-modal',
                buttons: []
            }, popup);

            const promptField = popup.find('textarea[name="prompt"]');
            popup.find('select[name="prompts"]').on('change', function() {
                const selectedPrompt = _.findWhere(
                    self.prompts, {entity_id: $(this).val()}
                );

                promptField.val('');
                if (selectedPrompt) {
                    promptField.val(selectedPrompt.text);
                }
            });

            popup.loader();
            popup.find('.action.send').on('click', function(e) {
                promptField.removeClass('error');
                if (promptField.val()) {
                    self.makeChatRequest(self.chatHistory.concat([{ role: 'user', content: promptField.val() }]));
                } else {
                    promptField.addClass('error');
                }

                return false;
            });

            popup.find('.chat').on('click', '.action.apply', function() {
                const resultTxt = _.unescape($(this).parents('.entry').find('.content pre').html());

                self.parentClass.setFieldContent(self.targetField, resultTxt);
                self.popup.modal('closeModal');

                return false;
            });

            popup.find('.chat').on('click', '.action.copy', function() {
                const button = $(this),
                    resultTxt = _.unescape(button.parents('.entry').find('.content pre').html());

                navigator.clipboard.writeText(resultTxt).then(
                    () => {
                        button.attr('data-message', $t('Saved!')).addClass('show-msg');
                    },
                    () => {
                        button.attr('data-message', $t('Error!')).addClass('show-msg');
                    }
                );

                setTimeout(function() {
                    button.removeClass('show-msg').removeAttr('data-message');
                }, 1000);

                return false;
            });

            return popup;
        },

        showPopup: function() {
            this.popup.modal('openModal');
        },

        showLoader: function() {
            this.popup.loader('show');
        },

        hideLoader: function() {
            this.popup.loader('hide');
        },

        /**
         * @param {Object} entry
         */
        addChatEntry: function(entry) {
            this.chatHistory.push(entry);
            this.updateChat();
        },

        /**
         * @param {Array} chatHistory
         */
        makeChatRequest: function(chatHistory) {
            const self = this;

            self.showLoader();
            self.parentClass.request(chatHistory)
            .done(function(data) {
                self.addChatEntry(chatHistory.pop());
                self.addChatEntry({ role: 'assistant', content: data.result });
            })
            .always(function() {
                self.hideLoader();
            });
        },

        updateChat: function() {
            const chatEl = this.popup.find('div.chat'),
                promptSelect = this.popup.find('select[name="prompts"]'),
                promptField = this.popup.find('textarea[name="prompt"]');

            promptSelect.val('');
            promptField.val('');
            chatEl.children('').remove();
            _.each(this.chatHistory, function(el) {
                const chatEntry = $(mageTemplate(chatEntryTmpl, {
                    role: el.role,
                    content: el.content,
                    copyTitle: $t('Copy to clipboard'),
                    applyTitle: $t('Apply to field and close popup')
                }));
                $(chatEl).append(chatEntry);
                chatEntry[0].scrollIntoView({behavior: 'smooth'});
            });
        }
    });

    return $.swissup.assistantPopup;
});
