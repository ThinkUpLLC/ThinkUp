<div align="right"><a href="javascript:;" title="See chart" onclick="{literal}${/literal}('#chart-{$i->id}').show(); return false;">see chart...</a>&nbsp;&nbsp;</div>
<div style="display:none" id="chart-{$i->id}">

<div id="count_history_{$i->id}"></div>

<script type="text/javascript">
// Load the Visualization API and the standard charts
google.load('visualization', '1.0');
// Set a callback to run when the Google Visualization API is loaded.
google.setOnLoadCallback(drawChart{$i->id});
{literal}

function drawChart{/literal}{$i->id}() {literal}{
  var formatter_date = new google.visualization.DateFormat({formatType: 'medium'});
  var formatter = new google.visualization.NumberFormat({fractionDigits: 0});
  {/literal}
  var count_history_data_{$i->id} = new google.visualization.DataTable(
  {$i->related_data.vis_data});
  formatter.format(count_history_data_{$i->id}, 1);
  formatter_date.format(count_history_data_{$i->id}, 0);
{literal}
  var count_history_chart_{/literal}{$i->id}{literal} = new google.visualization.ChartWrapper({
  {/literal}
      containerId: 'count_history_{$i->id}',
      {literal}
      chartType: 'LineChart',
      dataTable: count_history_data_{/literal}{$i->id}{literal},
      options: {
          width: 625,
          height: 250,
          legend: "none",
          interpolateNulls: true,
          pointSize: 2,
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
  count_history_chart_{/literal}{$i->id}{literal}.draw();
  }
  {/literal}
</script>
{if $i->related_data.milestone.units_of_time && $i->related_data.trend && $i->related_data.trend != 0}
    Current growth rate: {if $i->related_data.trend > 0}<span style="color:green">+{else}<span style="color:red">{/if}{$i->related_data.trend|number_format}</span>/{$i->related_data.milestone.units_of_time|lower}
{/if}

</div>