define([
    'Magento_Catalog/js/components/visible-on-option/select',
    'uiRegistry'
], function (Element, registry) {
    'use strict';

    return Element.extend({
        initialize: function () {
            this._super();

            if (this.filterByLevel) {
                if (this.filterByLevel.level > 2) {
                    this.filter(0, 'for_first_level_only');
                }
            } else if (registry.get(this.provider).data.level > 2) {
                this.filter(0, 'for_first_level_only');
            }

            return this;
        }
    });
});
