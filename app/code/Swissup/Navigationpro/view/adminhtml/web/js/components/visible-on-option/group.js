define([
    'Magento_Ui/js/form/components/group',
    'Magento_Catalog/js/components/visible-on-option/strategy'
], function (Group, strategy) {
    'use strict';

    return Group.extend(strategy);
});
