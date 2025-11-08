/* global google */
define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'https://www.gstatic.com/charts/loader.js'
], function ($, alert) {
    'use strict';

    var self, url;

    return {
        /**
         * @param {String} ajaxCallUrl
         * @param {Object} trigger
         */
        init: function (ajaxCallUrl, trigger) {
            self = this;
            url = ajaxCallUrl;

            google.charts.load('current', {
                'packages': [
                    'corechart'
                ]
            });

            google.charts.setOnLoadCallback(function () {
                $(trigger).on('change', function () {
                    self.generateStatistics($(this).val());
                });
                $(trigger).change();
            });
        },

        /**
         * @param {Object} data
         */
        drawChart: function (data) {
            var options = {
                    title: $.mage.__('Banner Statistics'),
                    hAxis: {
                        title: '',
                        titleTextStyle: {
                            color: '#333'
                        }
                    },
                    vAxis: {
                        minValue: 0
                    }
                },
                chart = new google.visualization.AreaChart(
                    document.getElementById('banner-statistics')
                ),
                // arrayToDataTable is not used becase legacy-build.min.js
                // breaks Array.entries method wich returns Array instead of ArrayIterator
                dataTable = new google.visualization.DataTable();

            $.each(data[0], function (i, value) {
                dataTable.addColumn(typeof data[1][i], value);
            });
            data.shift();
            dataTable.addRows(data);

            chart.draw(dataTable, options);
        },

        /**
         * @param {String} type
         */
        generateStatistics: function (type) {
            $.ajax({
                method: 'POST',
                url: url,
                showLoader: true,
                dataType: 'json',
                data: {
                    //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                    form_key: window.FORM_KEY,
                    type: type
                    //jscs:enable requireCamelCaseOrUpperCaseIdentifiers
                }
            })
            .done(function (data) {
                if (data.error) {
                    return alert({
                        title: $.mage.__('Error'),
                        content: data.error
                    });
                }
                self.drawChart(data.statistic);
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                alert({
                    title: $.mage.__('Error'),
                    content: $.mage.__('An error occured:') + errorThrown
                });
            });
        }
    };
});
