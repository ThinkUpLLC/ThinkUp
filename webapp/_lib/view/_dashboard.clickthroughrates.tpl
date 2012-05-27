<h2>Clickthrough Rates</h2>
<div class="clearfix article">
        <div id="click_stats"></div>
</div>

<script type="text/javascript">
    // Load the Visualization API and the standard charts
    google.load('visualization', '1');
    // Set a callback to run when the Google Visualization API is loaded.
    google.setOnLoadCallback(drawClickthroughRatesChart);

    {literal}
    function drawClickthroughRatesChart() {
    {/literal}
        var click_stats_data = new google.visualization.DataTable({$click_stats_data});
        {literal}
        var formatter = new google.visualization.NumberFormat({fractionDigits: 0});
        formatter.format(click_stats_data, 1);
        var click_stats_chart = new google.visualization.ChartWrapper({
            containerId: 'click_stats',
            chartType: 'BarChart',
            dataTable: click_stats_data,
            options: {
                colors: ['#3c8ecc'],
                isStacked: true,
                width: 650,
                height: 250,
                chartArea:{left:300,height:"80%"},
                legend: 'none',
                hAxis: {
                    textStyle: { color: '#fff', fontSize: 1 }
                },
                vAxis: {
                    minValue: 0,
                    baselineColor: '#ccc',
                    textStyle: { color: '#999' },
                    gridlines: { color: '#eee' }
                },
            }
        });
        click_stats_chart.draw();
    }
{/literal}
</script>