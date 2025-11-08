define(['jquery', 'Magento_Ui/js/modal/alert'], function($, alert) {
    var self,
        url;

    return {
        init: function(ajaxCallUrl, IndexBtn) {
            self = this;
            url = ajaxCallUrl;

            $(IndexBtn).on('click', function() {
                self.indexOrders(0, 0);
            });
        },
        indexOrders: function(last_processed, processed) {
            $.ajax({
                method: "POST",
                url: url,
                showLoader: true,
                dataType: "json",
                data: {
                    last_processed: last_processed,
                    processed: processed,
                    from_date: $('#reviewreminder_initial_indexing_from_date').val(),
                    from_date_type: $('#reviewreminder_initial_indexing_from_date_type').val(),
                    stores: $('#reviewreminder_initial_indexing_store_view').val() ?
                        $('#reviewreminder_initial_indexing_store_view').val().join() : ''
                }
            })
            .done(function(data) {
                if (data.error) {
                    alert({
                        title: $.mage.__('Error'),
                        content: data.error
                    });
                    return;
                }
                if (!data.finished) {
                    self.indexOrders(data.last_processed, data.processed);
                } else {
                    var message = $.mage.__("Completed. {count} items were processed");
                    alert({
                        title: $.mage.__('Success'),
                        content: message.replace('{count}', data.processed)
                    });
                }
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                alert({
                    title: $.mage.__('Error'),
                    content: $.mage.__('An error occured:') + errorThrown
                });
            });
        }
    }
});
