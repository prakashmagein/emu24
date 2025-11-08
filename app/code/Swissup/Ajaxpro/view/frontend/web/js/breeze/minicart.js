define([
    'jquery',
    'Swissup_Ajaxpro/js/modal-manager'
], function ($, ModalManager) {
    'use strict';

    $.view('ajaxproMinicart', 'minicart', {
        component: 'Swissup_Ajaxpro/js/view/minicart',
        template: 'minicart',
        minicartSelector: '.ajaxpro-popup-minicart',

        /** [create description] */
        create: function () {
            this._super();

            this.element.closest('.block-ajaxpro').on('ajaxproModal:opened', function () {
                this.initSidebar();
            }.bind(this));
        }
    });
});
