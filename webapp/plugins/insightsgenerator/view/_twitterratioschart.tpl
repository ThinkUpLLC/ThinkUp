{if $i->related_data.bar_chart}

<div id="twitter_ratios_{$i->id}" class="chart"></div>

<script type="text/javascript">
// Load the Visualization API and the standard charts
google.load('visualization', '1.0');
// Set a callback to run when the Google Visualization API is loaded.
google.setOnLoadCallback(drawChart{$i->id});
{literal}

function drawChart{/literal}{$i->id}() {literal}{

{/literal}
  var twitter_ratios_data_{$i->id} = new google.visualization.DataTable(
  {$i->related_data.bar_chart});
  var c = window.tu.constants.colors;
  var color = c.{$color};
{literal}
  var twitter_ratios_chart_{/literal}{$i->id}{literal} = new google.visualization.ChartWrapper({
  {/literal}
      containerId: 'twitter_ratios_{$i->id}',
      {literal}
      chartType: 'PieChart',
      dataTable: twitter_ratios_data_{/literal}{$i->id}{literal},
      options: {
          height: 200,
          width: 290,
          pieHole: 0.4,
          legend: "none",
          interpolateNulls: true,
          pointSize: 4,
          colors : [color],
          hAxis: {
              baselineColor: '#eee',
              format: 'MMM d',
              textStyle: { color: '#999' },
              gridlines: { color: '#eee' }
          },
          vAxis: {
              baselineColor: '#eee',
              textStyle: { color: '#999' },
              gridlines: { color: '#eee' }
          },
      },
  });
  twitter_ratios_chart_{/literal}{$i->id}{literal}.draw();
  }
  {/literal}
</script>
{/if}