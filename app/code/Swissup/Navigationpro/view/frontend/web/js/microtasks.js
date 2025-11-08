define([
    'jquery'
], function ($) {
    'use strict';

    var methods = [
        'addClass',
        'after',
        'append',
        'appendTo',
        'before',
        'hide',
        'insertAfter',
        'insertBefore',
        'off',
        'on',
        'prepend',
        'prependTo',
        'remove',
    ];

    // Copied from underscore 1.13. Compatibilty with Magento 2.3.* and <=2.4.3
    function _chunk(array, count) {
        var result = [], i = 0, length = array.length;

        if (!count) {
            return result;
        }

        while (i < length) {
            result.push(Array.prototype.slice.call(array, i, i += count));
        }

        return result;
    }

    $.fn.microtasks = function (chunkSize = 1200) {
        if (this.microtasksProxy) {
            return this.microtasksProxy;
        }

        this.microtasksProxy = new Proxy(this, {
            get(target, prop) {
                if (!methods.includes(prop)) {
                    return target[prop];
                }

                return (...args) => {
                    _chunk(target, chunkSize).forEach(chunk => setTimeout(() => $(chunk)[prop](...args)));
                    return target.microtasksProxy;
                };
            }
        });

        return this.microtasksProxy;
    };
});
