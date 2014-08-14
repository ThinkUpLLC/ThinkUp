{if $i->related_data.bar_chart}

<div id="network_stylestats_{$i->id}" class="chart"></div>

<script type="text/javascript">
// Load the Visualization API and the standard charts
google.load('visualization', '1.0');
// Set a callback to run when the Google Visualization API is loaded.
google.setOnLoadCallback(drawChart{$i->id});
{literal}

function drawChart{/literal}{$i->id}() {literal}{

{/literal}
  var network_stylestats_data_{$i->id} = new google.visualization.DataTable(
  {$i->related_data.bar_chart});
  var c = window.tu.constants.colors;
  var color = c.{$color};
  var color_dark = c.{$color}
{literal}
  var network_stylestats_chart_{/literal}{$i->id}{literal} = new google.visualization.ChartWrapper({
  {/literal}
      containerId: 'network_stylestats_{$i->id}',
      {literal}
      chartType: 'PieChart',
      dataTable: network_stylestats_data_{/literal}{$i->id}{literal},
      options: {
          height: 200,
          width: 290,
          pieHole: 0.4,
          interpolateNulls: true,
          pointSize: 4,
          colors: {/literal}[c.{$color}, c.{$color}_dark, c.{$color}_darker],{literal}
      },
  });
  network_stylestats_chart_{/literal}{$i->id}{literal}.draw();
  }
  {/literal}
</script>
{/if}