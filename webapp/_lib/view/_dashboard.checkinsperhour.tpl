<div class="section">
    <h2>Checkins Per Hour</h2>
      <div id="checkins_per_hour"></div>
    <script type="text/javascript">
    // Load the Visualization API and the standard charts
    google.load('visualization', '1');
    // Set a callback to run when the Google Visualization API is loaded.
    google.setOnLoadCallback(drawCheckinsPerHourAllTimeChart);

    {literal}
    function drawCheckinsPerHourAllTimeChart() {
    {/literal}
        var checkins_per_hour_data = new google.visualization.DataTable({$checkins_per_hour});
        {literal}

        var checkins_per_hour = new google.visualization.ChartWrapper({
            containerId: 'checkins_per_hour',
            chartType: 'ColumnChart',
            dataTable: checkins_per_hour_data,
            options: {
                colors: ['#3c8ecc', '#3e5d9a' ],
                width: 708,
                height: 300,
                legend: 'none',
                hAxis: {
                    title: 'Hour of Day',
                    textStyle: { 
                        color: '#999'
                    }
                },
                vAxis: {
                    title: 'Number of Checkins',
                    minValue: 0,
                    baselineColor: '#ccc',
                    textStyle: { color: '#999' },
                },
            }
        });
        checkins_per_hour.draw();
    }
{/literal}
</script>

</div>
