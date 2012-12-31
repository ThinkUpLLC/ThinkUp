{include file=$tpl_path|cat:'_header.tpl'}

{if  !$expand}
<div class="pull-right detail-btn"><button class="btn btn-info btn-mini" data-toggle="collapse" data-target="#chart-{$i->id}"><i class="icon-signal icon-white"></i></button></div>
{/if}

<span class="label label-{if $i->emphasis eq '1'}inverse{elseif $i->emphasis eq '2'}success{elseif $i->emphasis eq '3'}error{else}info{/if}"><i class="icon-white icon-list"></i> <a href="?u={$i->instance->network_username}&n={$i->instance->network}&d={$i->date|date_format:'%Y-%m-%d'}&s={$i->slug}">{$i->prefix}</a></span> 

{$i->text|link_usernames_to_twitter}

<div class="insight-attachment-detail post">
    {$i->related_data[0]->title} {$i->related_data[0]->expanded_url}
</div>

{if  !$expand}
<div class="collapse in" id="chart-{$i->id}">
{/if}

    <div id="click_totals_{$i->id}"></div>
    <script type="text/javascript">
        // Load the Visualization API and the standard charts
        google.load('visualization', '1');
        // Set a callback to run when the Google Visualization API is loaded.
        google.setOnLoadCallback(drawResponseRatesChart{$i->id} );

        {literal}
        function drawResponseRatesChart{/literal}{$i->id}{literal}() {
        {/literal}
            var click_totals_data_{$i->id} = new google.visualization.DataTable({$i->related_data[1]});

            {literal}
            var click_totals_chart_{/literal}{$i->id}{literal} = new google.visualization.ChartWrapper({
              containerId: 'click_totals_{/literal}{$i->id}{literal}',
              chartType: 'BarChart',
              dataTable: click_totals_data_{/literal}{$i->id}{literal},
              options: {
                  colors: ['#3e5d9a', '#3c8ecc', '#BBCCDD'],
                  isStacked: true,
                  width: 650,
                  height: 250,
                  chartArea:{left:300,height:"80%"},
                  legend: 'bottom',
                  hAxis: {
                    textStyle: { color: '#fff', fontSize: 1 }
                  },
                  vAxis: {
                    minValue: 0,
                    baselineColor: '#ccc',
                    textStyle: { color: '#999' },
                    gridlines: { color: '#eee' }
                  },
                }
            });
            click_totals_chart_{/literal}{$i->id}{literal}.draw();
        }
        {/literal}
    </script>

{if  !$expand}
</div>
{/if}

{include file=$tpl_path|cat:'_footer.tpl'}
