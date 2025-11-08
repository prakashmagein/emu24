define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'Swissup_Ajaxpro/js/modal-manager',
], function ($, customerData, ModalManager) {
    'use strict';

    var cart = $('#ajaxpro-checkout\\.cart');

    const hasNoActiveAjaxRequest = () => {
        return !$.active;
    },
    isCartEmpty = () => {
        return cart.html() === '';
    };

    const addObservers = function () {
        $('[data-block=\'minicart\'] a.showcart').off().on('click', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            if (isCartEmpty()) {
                customerData.reload(['ajaxpro-cart'], false);
            } else {
                ModalManager.show('checkout.cart');
            }

            return false;
        });
    };

    const reloadBlock = () => {
        const cartElementId = 'ajaxpro-checkout.cart';
        ModalManager.unregister(cartElementId);
        customerData.reload(['ajaxpro-cart'], false)
            .done(function() {
                const blockElement = cart.closest('.block-ajaxpro');
                if (blockElement) {
                    ModalManager.register(cartElementId, blockElement)
                        .then(addObservers);
                }
            });
    };

    const onReady = function () {
        var interval = setInterval(function () {
            if (isCartEmpty() &&
                hasNoActiveAjaxRequest() &&
                ModalManager.has('ajaxpro-checkout.cart')
            ) {
                reloadBlock();
                clearInterval(interval);
            }
        }, 100);

        setTimeout(() => {
            clearInterval(interval);
        }, 2000);

        addObservers();
    };

    return {
        component: 'Swissup_Ajaxpro/js/view/minicart/override',
        'Swissup_Ajaxpro/js/view/minicart/override': function (settings) {
            if (settings?.override_minicart === true) {
                $(document).ready(onReady);
                $(document).on('breeze:load', onReady);
            }
        }
    }
});
