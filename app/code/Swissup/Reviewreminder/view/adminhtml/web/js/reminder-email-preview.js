define(['jquery', 'Magento_Ui/js/modal/alert'], function($, alert) {
    var self,
        url,
        entityId,
        containerId;

    return {
        init: function(ajaxCallUrl, id, frameId) {
            self = this;
            url = ajaxCallUrl;
            entityId = id;
            containerId = frameId;

            $(document).on('tabsactivate', function(event, ui) {
                var anchor = $(ui.newTab).find('a');
                if (anchor.attr('id') == 'index_tabs_preview') {
                    $.ajax({
                        method: "POST",
                        url: url,
                        showLoader: true,
                        dataType: "json",
                        data: {
                            entity_id: entityId
                        }
                    })
                    .done(self.showResponse)
                    .fail(function(jqXHR, textStatus, errorThrown) {
                        alert({
                            title: $.mage.__('Error'),
                            content: $.mage.__('An error occured:') + errorThrown
                        });
                    });

                }
            });
        },
        showResponse: function(data) {
            var iframeElementContainer = document.getElementById(containerId).contentDocument;
            iframeElementContainer.open();
            iframeElementContainer.writeln(data.outputHtml);
            iframeElementContainer.close();
        }
    }
});
