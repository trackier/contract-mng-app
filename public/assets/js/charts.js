/**
 * Author: vNative Dev Team
 * Email: info@vnative.com
 * File: Chartjs
 */
(function($) {
    "use strict";

    var Hcharts = function() {};

    //creates line chart
    Hcharts.prototype.createLineChart = function(element, data, xkeys, title) {
        Highcharts.chart(element, {
            title: {
                text: title
            },
            xAxis: {
                categories: xkeys
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle'
            },
            series: data,

            responsive: {
                rules: [{
                    condition: {
                        maxWidth: 500
                    },
                    chartOptions: {
                        legend: {
                            layout: 'horizontal',
                            align: 'center',
                            verticalAlign: 'bottom'
                        }
                    }
                }]
            }
        });
    },
    Hcharts.prototype.createBarChart = function(element, data, seriesTitle, title) {
        Highcharts.chart(element, {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: title
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                        style: {
                            color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                        }
                    }
                }
            },
            series: [{
                name: seriesTitle,
                colorByPoint: true,
                data: data
            }],
             credits: {
                  enabled: false
            },
            exporting: { 
                enabled: false 
            }
        });
    },
    //init
    $.Hcharts = new Hcharts, $.Hcharts.Constructor = Hcharts
}(window.jQuery));
