define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/components/button',
    'Magento_Ui/js/modal/confirm',
    'mage/translate'
], function ($, _, registry, Button, modalConfirm, $t) {
    'use strict';

    return Button.extend({
        /**
         * Click handler
         */
        action: function () {
            var self = this;

            modalConfirm({
                title: $t('Are you sure?'),
                content: $t('This operation will reset current settings'),
                actions: {
                    /**
                     * 'Confirm' action handler.
                     */
                    confirm: function () {
                        self.resetSettings();
                    }
                }
            });
        },

        /**
         * Apply dropdown settings
         */
        resetSettings: function () {
            //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
            var provider = registry.get(this.provider),
                options = this.options,
                menuId = provider.data.menu_id;

            $('body').trigger('processStart');

            $.ajax(provider.menu_settings_url, {
                method: 'get',
                cache: false,
                data: {
                    menu_id: menuId
                }
            }).done(function (response) {
                var data = response.data[options.scope],
                    level = provider.data.level - 1;

                if (!data) {
                    return;
                }

                $('body').trigger('processStop');

                if (data['level' + level]) {
                    data = data['level' + level];
                } else {
                    data = data['default'];
                }

                _.each(data, function (value, key) {
                    var field = registry.get(options.target + '.' + key);

                    if (!field) {
                        return;
                    }

                    field.value(value);
                });
            });
            //jscs:enable requireCamelCaseOrUpperCaseIdentifiers
        }
    });
});
