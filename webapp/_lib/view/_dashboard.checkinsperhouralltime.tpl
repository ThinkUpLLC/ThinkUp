<div class="section">
    <h2>Checkins Per Hour All Time</h2>
      <div id="checkins_all_time"></div>
    <script type="text/javascript">
    // Load the Visualization API and the standard charts
    google.load('visualization', '1');
    // Set a callback to run when the Google Visualization API is loaded.
    google.setOnLoadCallback(drawCheckinsPerHourAllTimeChart);

    {literal}
    function drawCheckinsPerHourAllTimeChart() {
    {/literal}
        var checkins_all_data = new google.visualization.DataTable({$checkins_per_hour_all_time});
        {literal}

        var checkins_per_hour_all_time = new google.visualization.ChartWrapper({
            containerId: 'checkins_all_time',
            chartType: 'ColumnChart',
            dataTable: checkins_all_data,
            options: {
                colors: ['#3c8ecc'],
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
        checkins_per_hour_all_time.draw();
    }
{/literal}
</script>
              
</div>
