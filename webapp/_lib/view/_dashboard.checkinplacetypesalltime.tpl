<div class="section">
      <h2>The Types Of Places You Visit</h2>
      <div id="place_types"></div>
     <script type="text/javascript">
    // Load the Visualization API and the standard charts
    google.load('visualization', '1');
    // Set a callback to run when the Google Visualization API is loaded.
    google.setOnLoadCallback(drawPlaceTypeChart);

    {literal}
    function drawPlaceTypeChart() {
    {/literal}
      var place_type_data = new google.visualization.DataTable({$checkins_by_type});
      {literal}
      var place_type_chart = new google.visualization.ChartWrapper({
          containerId: 'place_types',
          chartType: 'PieChart',
          dataTable: place_type_data,
          options: {
              titleTextStyle: {color: '#848884', fontSize: 19},
              width: 708,
              height: 300,
              sliceVisibilityThreshold: 1/100,
              pieSliceText: 'label',
          }
      });
      place_type_chart.draw();
    }
    {/literal}
</script>
</div>
