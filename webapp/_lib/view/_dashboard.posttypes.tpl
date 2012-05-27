  <div class="alpha">
      <h2>Post Types</span></h2>
      <div class="small prepend article">
        <div id="post_types"></div>
       </div>
       <div class="stream-pagination"><small style="color:#666;padding:5px;">
          {$instance->percentage_replies|round}% posts are replies<br>
          {$instance->percentage_links|round}% posts contain links
          </small>
       </div>
</div>

<script type="text/javascript">
    // Load the Visualization API and the standard charts
    google.load('visualization', '1');
    // Set a callback to run when the Google Visualization API is loaded.
    google.setOnLoadCallback(drawPostTypesChart);

    {literal}
    function drawPostTypesChart() {
      var replies = {/literal}{$instance->percentage_replies|round};
      var links = {$instance->percentage_links|round};
      {literal}
      if (typeof(replies) != 'undefined') {
        var post_types = new google.visualization.DataTable();
        post_types.addColumn('string', 'Type');
        post_types.addColumn('number', 'Percentage');
        post_types.addRows([
            ['Conversationalist', {v: replies/100, f: replies + '%'}], 
            ['Broadcaster', {v: links/100, f: links + '%'}]
        ]);

        var post_type_chart = new google.visualization.ChartWrapper({
            containerId: 'post_types',
            chartType: 'ColumnChart',
            dataTable: post_types,
            options: {
                colors: ['#3c8ecc'],
                width: 300,
                height: 200,
                legend: 'none',
                hAxis: {
                    minValue: 0,
                    maxValue: 1,
                    textStyle: { color: '#000' },
                },
                vAxis: {
                    textStyle: { color: '#666' },
                    gridlines: { color: '#ccc' },
                    format:'#,###%',
                    baselineColor: '#ccc',
                },
            }
        });
        post_type_chart.draw();
      }
   }
   {/literal}
</script>


