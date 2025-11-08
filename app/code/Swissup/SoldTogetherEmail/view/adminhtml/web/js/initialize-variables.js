define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Email/js/variables'
], function ($, wrapper) {
    'use strict';

    return function (options) {
        window.Variables.openVariableChooser = wrapper.wrap(window.Variables.openVariableChooser, function () {
            var args = Array.prototype.slice.call(arguments),
                originalAction = args.shift(args),
                variables = args[0] || [];

            variables.forEach((group) => {
                var addMyVariables = false;

                if (group.value && group.value.forEach) {
                    group.value.forEach((item) => {
                        if (item.value) {
                            if (item.value.indexOf('$order_id') !== -1
                                || item.value.indexOf(' order_data') !== -1
                            ) {
                                // It looks like variables group has variables for to Sales Order.
                                // So we assume it is Sales email.
                                // And it is possible to use Sold Together blocks.
                                addMyVariables = true;
                            }
                        }

                    });

                    if (addMyVariables && group.value.concat) {
                        group.value = group.value.concat(options.variables || [])
                    }
                }
            });

            args[0] = variables;

            return originalAction.apply(null, args);
        });
    }
});
