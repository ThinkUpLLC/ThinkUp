{include file=$tpl_path|cat:'_header.tpl'}

{if  !$expand}
<div class="pull-right detail-btn"><button class="btn btn-info btn-xs" data-toggle="collapse" data-target="#chart-{$i->id}"><i class="fa fa-signal icon-white"></i></button></div>
{/if}

<span class="label label-{if $i->emphasis eq '1'}info{elseif $i->emphasis eq '2'}success{elseif $i->emphasis eq '3'}error{else}info{/if}"><i class="fa icon-white fa-certificate"></i> <a href="?u={$i->instance->network_username}&amp;n={$i->instance->network}&amp;d={$i->date|date_format:'%Y-%m-%d'}&amp;s={$i->slug}">{$i->headline}</a></span> 

<i class="fa fa-{$i->instance->network}{if $i->instance->network eq 'google+'} fa-google-plus{/if} icon-muted"></i>
{$i->text|link_usernames_to_twitter}

<div class="insight-attachment-detail post">
    {include file=$tpl_path|cat:"_post.tpl" post=$i->related_data[0] hide_insight_header=true}
</div>

{if !$expand}
<div class="collapse in" id="chart-{$i->id}">
{/if}

    <div id="response_rates_{$i->id}">&nbsp;</div>
    <script type="text/javascript">
        // Load the Visualization API and the standard charts
        google.load('visualization', '1');
        // Set a callback to run when the Google Visualization API is loaded.
        google.setOnLoadCallback(drawChart{$i->id} );

        {literal}
        function drawChart{/literal}{$i->id}{literal}() {
        {/literal}
            var response_rates_data_{$i->id} = new google.visualization.DataTable({$i->related_data[1]});

            {literal}
            var response_rates_chart_{/literal}{$i->id}{literal} = new google.visualization.ChartWrapper({
              containerId: 'response_rates_{/literal}{$i->id}{literal}',
              chartType: 'BarChart',
              dataTable: response_rates_data_{/literal}{$i->id}{literal},
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
            response_rates_chart_{/literal}{$i->id}{literal}.draw();
        }
        {/literal}
    </script>

{if  !$expand}
</div>
{/if}

{include file=$tpl_path|cat:'_footer.tpl'}