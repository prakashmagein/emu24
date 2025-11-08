define([
    'Magento_Ui/js/view/messages',
    'Magento_Ui/js/model/messages'
], function (Component, Messages) {
    'use strict';

    return Component.extend({
        /** @inheritdoc */
        initialize: function (config, messageContainer) {
            this._super(config, messageContainer || new Messages());
        }
    });
});
