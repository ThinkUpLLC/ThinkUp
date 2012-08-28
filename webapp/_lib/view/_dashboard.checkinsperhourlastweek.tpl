<div class="section">
<h2>Checkins Per Hour This Week</h2>
<div id="checkins_last_week"></div>
     <script type="text/javascript">
     // Load the Visualization API and the standard charts
     google.load('visualization', '1');
     // Set a callback to run when the Google Visualization API is loaded.
     google.setOnLoadCallback(drawCheckinsPerHourChart);
 
     {literal}
     function drawCheckinsPerHourChart() {
     {/literal}
         var checkins_week_data = new google.visualization.DataTable({$checkins_per_hour_last_week});
         {literal}
         var checkins_per_hour_last_week = new google.visualization.ChartWrapper({
             containerId: 'checkins_last_week',
             chartType: 'ColumnChart',
             dataTable: checkins_week_data,
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
        checkins_per_hour_last_week.draw();
     }
 {/literal}
 </script>
 </div>
