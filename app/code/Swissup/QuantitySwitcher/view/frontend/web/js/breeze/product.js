(function () {
    'use strict';

    $.widget('qtySwitcher', {
        component: 'Swissup_QuantitySwitcher/js/product',

        create: function () {
            $.qtySwitcher(this.options, this.element);
        },

        destroy: function () {
            this.element.parent().find('.qty-switcher-dec,.qty-switcher-inc,.action.trigger,.dropdown').remove();
            this.element.unwrap();
            this._super();
        }
    });
})();
