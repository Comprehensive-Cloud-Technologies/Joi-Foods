<script>
$(document).ready(function() {
    var moduleData = <?php echo json_encode($module_stats); ?>;
    var trendData = <?php echo json_encode($monthly_trend); ?>;

    // Module-wise Donut Chart
    if (document.getElementById('module_orders_chart')) {
        var moduleLabels = [];
        var moduleSeries = [];
        var moduleColors = [];
        var colorMap = { 'QSR': '#5156be', 'KOT': '#ffbf53', 'PREMEAL': '#4ba6ef' };

        for (var key in moduleData) {
            moduleLabels.push(key);
            moduleSeries.push(moduleData[key].order_count);
            moduleColors.push(colorMap[key] || '#999');
        }

        var donutOptions = {
            series: moduleSeries,
            chart: {
                type: 'donut',
                height: 240,
                fontFamily: 'inherit'
            },
            labels: moduleLabels,
            colors: moduleColors,
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            name: { show: true, fontSize: '13px', color: '#718096' },
                            value: { show: true, fontSize: '20px', fontWeight: 600, color: '#2d3748' },
                            total: {
                                show: true,
                                label: 'Total',
                                fontSize: '13px',
                                color: '#718096',
                                formatter: function(w) {
                                    return w.globals.spikeData && w.globals.spikeData.length
                                        ? w.globals.spikeData.reduce(function(a, b) { return a + b; }, 0)
                                        : w.globals.series.reduce(function(a, b) { return a + b; }, 0);
                                }
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            legend: { show: false },
            stroke: { width: 2, colors: ['#fff'] },
            tooltip: {
                y: {
                    formatter: function(val) { return val + ' orders'; }
                }
            }
        };

        new ApexCharts(document.getElementById('module_orders_chart'), donutOptions).render();
    }

    // Contribution Trend Bar Chart
    if (document.getElementById('contribution_trend_chart')) {
        var labels = trendData.map(function(m) { return m.label; });
        var companyData = trendData.map(function(m) { return m.company_share; });
        var employeeData = trendData.map(function(m) { return m.employee_share; });

        var barOptions = {
            series: [{
                name: 'Company Contribution',
                data: companyData
            }, {
                name: 'Employee Contribution',
                data: employeeData
            }],
            chart: {
                type: 'bar',
                height: 330,
                toolbar: { show: false },
                fontFamily: 'inherit'
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    columnWidth: '50%'
                }
            },
            dataLabels: { enabled: false },
            colors: ['#fd625e', '#5156be'],
            xaxis: {
                categories: labels,
                labels: { style: { colors: '#718096', fontSize: '12px' } }
            },
            yaxis: {
                labels: {
                    formatter: function(val) {
                        if (val >= 1000) return 'Rs. ' + (val / 1000).toFixed(1) + 'k';
                        return 'Rs. ' + val;
                    },
                    style: { colors: '#718096', fontSize: '12px' }
                }
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return 'Rs. ' + val.toLocaleString('en-IN', { minimumFractionDigits: 2 });
                    }
                }
            },
            legend: { position: 'top', horizontalAlign: 'right' },
            grid: { borderColor: '#e2e8f0', strokeDashArray: 3 }
        };

        new ApexCharts(document.getElementById('contribution_trend_chart'), barOptions).render();
    }
});
</script>
