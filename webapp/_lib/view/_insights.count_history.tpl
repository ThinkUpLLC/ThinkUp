<span class="label label-{if $i->emphasis eq '1'}inverse{elseif $i->emphasis eq '2'}success{elseif $i->emphasis eq '3'}error{else}info{/if}">{$i->prefix}</span> 
                <i class="icon-star-empty"></i>
                {$i->text}

<div class="pull-right detail-btn"><button class="btn-mini" data-toggle="collapse" data-target="#chart-{$i->id}"><i class="icon-signal"></i></button></div>

<div class="collapse in" id="chart-{$i->id}">

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
          width: 800,
          height: 200,
          legend: "none",
          interpolateNulls: true,
          pointSize: 4,
          colors : ['#31C22D'],
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