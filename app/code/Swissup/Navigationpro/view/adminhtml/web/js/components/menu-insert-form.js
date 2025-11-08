define([
    'Magento_Ui/js/form/components/insert-form',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'uiRegistry'
], function (InsertForm, uiAlert, $t, registry) {
    'use strict';

    return InsertForm.extend({
        defaults: {
            listens: {
                responseData: 'onResponse'
            }
        },

        /**
         * [initialize description]
         */
        initialize: function () {
            this._super();

            //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
            this.params.menu_id = registry.get(this.provider).data.menu_id;
            this.params.parent_id = registry.get(this.provider).data.item_id;
            this.params.store_id = registry.get(this.provider).data.store_id;
            //jscs:enable requireCamelCaseOrUpperCaseIdentifiers

            return this;
        },

        /**
         * @param {Object} responseData
         */
        onResponse: function (responseData) {
            if (responseData.error) {
                uiAlert({
                    title: $t('Error'),
                    content: responseData.message
                });
            }
        }
    });
});
