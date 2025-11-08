define([
    'jquery'
], function ($) {

    return {
        url: '',

        init: function (ajaxCallUrl, IndexBtn) {
            this.url = ajaxCallUrl;
            $(IndexBtn).attr('onclick', 'return false');
            $(IndexBtn).on('click', () => {
                this.indexOrders();

                return false;
            });
        },

        indexOrders: function (step, count) {
            var data = {
                'form_key': window.FORM_KEY
            }

            if (typeof step !== 'undefined')
                data.step = step;

            if (typeof count !== 'undefined')
                data.count = count;

            $.ajax({
                method: "POST",
                url: this.url,
                showLoader: true,
                dataType: "json",
                data: data
            })
            .done((data) => {
                if (data.error) {
                    this.showError(data.error);

                    return;
                }
                if (!data.finished) {
                    $('.loading-mask .popup-inner').text(data.loaderText);
                    this.indexOrders(data.nextStep, data.count);
                } else {
                    location.reload();
                }
            })
            .fail((jqXHR, textStatus, errorThrown) => {
                this.showError(textStatus == 'parsererror' ? jqXHR.responseText : errorThrown);
            });
        },

        showError: function (msg) {
            require([
                'Magento_Ui/js/modal/alert',
                'mage/translate'
            ], function (alert) {
                alert({
                    title: $.mage.__('Error'),
                    content: msg
                });
            });
        }
    }
});
