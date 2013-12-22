{include file=$tpl_path|cat:'_header.tpl'}

{if  !$expand}
<div class="pull-right detail-btn"><button class="btn btn-info btn-mini" data-toggle="collapse" data-target="#chart-{$i->id}"><i class="icon-signal icon-white"></i></button></div>
{/if}

<span class="label label-{if $i->emphasis eq '1'}info{elseif $i->emphasis eq '2'}success{elseif $i->emphasis eq '3'}error{else}info{/if}">
<i class="icon-white icon-circle-eye"></i>
<a href="?u={$i->instance->network_username}&n={$i->instance->network}&d={$i->date|date_format:'%Y-%m-%d'}&s={$i->slug}">{$i->headline}</a></span>
<i class="icon-{$i->instance->network}{if $i->instance->network eq 'google+'} icon-google-plus{/if} icon-muted"></i>
{$i->text}

{if !$expand}
<div class="collapse in" id="chart-{$i->id}">
{/if}

    <div id="view_duration_{$i->id}">&nbsp;</div>
    <script type="text/javascript">
        // Load the Visualization API and the standard charts
        google.load('visualization', '1');
        // Set a callback to run when the Google Visualization API is loaded.
        google.setOnLoadCallback(drawChart{$i->id} );
        {literal}
        function drawChart{/literal}{$i->id}{literal}() {
        {/literal}
            {literal}
            var view_duration_data_{/literal}{$i->id} = new google.visualization.DataTable({$i->related_data[0]});
            {literal}
            var view_duration_chart_{/literal}{$i->id}{literal} = new google.visualization.ChartWrapper({
              containerId: 'chart-{/literal}{$i->id}{literal}',
              chartType: 'BarChart',
              dataTable: view_duration_data_{/literal}{$i->id}{literal},
              options: {
                  width: 650,
                  height: 250,
                  chartArea:{left:300,height:"80%"},
                  hAxis: {
                    textStyle: { color: '#999', fontSize: 10 }
                  },
                  vAxis: {
                    minValue: 0,
                    baselineColor: '#ccc',
                    textStyle: { color: '#999' },
                    gridlines: { color: '#eee' }
                  }
                }
            });
            view_duration_chart_{/literal}{$i->id}{literal}.draw();
        }
        {/literal}
    </script>

{if  !$expand}
</div>
{/if}

{include file=$tpl_path|cat:'_footer.tpl'}
