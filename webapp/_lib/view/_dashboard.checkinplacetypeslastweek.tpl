<div class="section">
      <h2>This Week's Places</h2>
      <div class="article"> 
        <div id="place_types_last_week"></div>
      </div>
     <script type="text/javascript">
    // Load the Visualization API and the standard charts
    google.load('visualization', '1');
    // Set a callback to run when the Google Visualization API is loaded.
    google.setOnLoadCallback(drawPlaceTypeLastWeekChart);

    {literal}
    function drawPlaceTypeLastWeekChart() {
    {/literal}
      var place_type_last_week_data = new google.visualization.DataTable({$checkins_by_type_last_week});
      {literal}
      var place_type_last_week_chart = new google.visualization.ChartWrapper({
          containerId: 'place_types_last_week',
          chartType: 'PieChart',
          dataTable: place_type_last_week_data,
          options: {
              titleTextStyle: {color: '#848884', fontSize: 19},
              width: 320,
              height: 320,
              sliceVisibilityThreshold: 1/100,
              pieSliceText: 'label',
          }
      });
      place_type_last_week_chart.draw();
    }
    {/literal}
</script>
</div>
