{include file=$tpl_path|cat:'_header.tpl'}

{if  !$expand}
<div class="pull-right detail-btn"><button class="btn btn-info btn-mini" data-toggle="collapse" data-target="#chart-{$i->id}"><i class="icon-signal icon-white"></i></button></div>
{/if}

<span class="label label-{if $i->emphasis eq '1'}info{elseif $i->emphasis eq '2'}success{elseif $i->emphasis eq '3'}error{else}info{/if}">
<i class="icon-adjust"></i>
<a href="?u={$i->instance->network_username}&n={$i->instance->network}&d={$i->date|date_format:'%Y-%m-%d'}&s={$i->slug}">{$i->headline}</a></span>
<i class="icon-{$i->instance->network}{if $i->instance->network eq 'google+'} icon-google-plus{/if} icon-muted"></i>
{$i->text|link_usernames}

{if !$expand}
<div class="collapse in" id="chart-{$i->id}">
{/if}

    <div id="like_dislikes_{$i->id}">&nbsp;</div>
    <script type="text/javascript">
        // Load the Visualization API and the standard charts
        google.load('visualization', '1');
        // Set a callback to run when the Google Visualization API is loaded.
        google.setOnLoadCallback(drawChart{$i->id} );
        {literal}
        function drawChart{/literal}{$i->id}{literal}() {
        {/literal}
            {literal}
            var likes_dislikes_data_{/literal}{$i->id}{literal} = google.visualization.arrayToDataTable([
                ['Metric', 'Quantity'],
                ['Likes', {/literal}{$i->related_data->likes}{literal}],
                ['Dislikes',{/literal}{$i->related_data->dislikes}{literal}]
            ]);

            var likes_dislikes_chart_{/literal}{$i->id}{literal} = new google.visualization.ChartWrapper({
              containerId: 'chart-{/literal}{$i->id}{literal}',
              chartType: 'PieChart',
              dataTable: likes_dislikes_data_{/literal}{$i->id}{literal},
              options: {
                  width: 650,
                  height: 250,
                  chartArea:{left:300,height:"80%"},
                  legend: 'bottom',
                  colors: ['#7DD3F0', '#E6B8D4']
                }
            });
            likes_dislikes_chart_{/literal}{$i->id}{literal}.draw();
        }
        {/literal}
    </script>

{if  !$expand}
</div>
{/if}

{include file=$tpl_path|cat:'_footer.tpl'}
