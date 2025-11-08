define([
    'jquery',
    'mage/apply/main',
    'Magento_Ui/js/lib/knockout/bootstrap'
], function ($, mage) {
    'use strict';

    $.ajaxSetup({
        cache: false
    });

    /**
     * Init all components defined via data-mage-init attribute.
     */
    var idleCallback = window.requestIdleCallback || window.setTimeout;
    idleCallback(function () {
        $(mage.apply);
    });
});
