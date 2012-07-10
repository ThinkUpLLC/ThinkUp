{if $all_time_clients_usage}
<div class="omega">
     <h2>Client Usage <span class="detail">(all posts)</span></h2>
     <div class="article">
     <div id="client_usage"></div>
     </div>
     <div class="stream-pagination">
     <small style="color:#666;padding:5px;">Recently posting about {$instance->posts_per_day|round} times a day{if $latest_clients_usage}, mostly using {foreach from=$latest_clients_usage key=name item=num_posts name=foo}{$name}{if !$smarty.foreach.foo.last} and {/if}{/foreach}{/if}</small>
     </div>
</div>

<script type="text/javascript">
    // Load the Visualization API and the standard charts
    google.load('visualization', '1');
    // Set a callback to run when the Google Visualization API is loaded.
    google.setOnLoadCallback(drawClientUsageChart);

    {literal}
    function drawClientUsageChart() {
    {/literal}
      var client_usage_data = new google.visualization.DataTable({$all_time_clients_usage});
      {literal}
      var formatter = new google.visualization.NumberFormat({fractionDigits: 0});
      var formatter_date = new google.visualization.DateFormat({formatType: 'medium'});
      formatter.format(client_usage_data, 1);
      var client_usage_chart = new google.visualization.ChartWrapper({
          containerId: 'client_usage',
          // chartType: 'ColumnChart',
          chartType: 'PieChart',
          dataTable: client_usage_data,
          options: {
              titleTextStyle: {color: '#848884', fontSize: 19},
              width: 300,
              height: 300,
              sliceVisibilityThreshold: 1/100,
              chartArea: { width: '100%' },
              pieSliceText: 'label',
          }
      });
      client_usage_chart.draw();
    }
    {/literal}
</script>
{/if}