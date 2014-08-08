{include file=$tpl_path|cat:'_header.tpl'}

{if !$expand}
<div class="pull-right detail-btn"><button class="btn btn-info btn-mini" data-toggle="collapse" data-target="#chart-{$i->id}"><i class="icon-signal icon-white"></i></button></div>
{/if}

<span class="label label-{if $i->emphasis eq '1'}info{elseif $i->emphasis eq '2'}success{elseif $i->emphasis eq '3'}error{else}info{/if}"><i class="icon-white icon-gift"></i> <a href="?u={$i->instance->network_username}&n={$i->instance->network}&d={$i->date|date_format:'%Y-%m-%d'}&s={$i->slug}">{$i->headline}</a></span>

<i class="icon-{$i->instance->network}{if $i->instance->network eq 'google+'} icon-google-plus{/if} icon-muted"></i>
{$i->text|link_usernames_to_twitter}

<div class="insight-attachment-detail post">
    {include file=$tpl_path|cat:"_post.tpl" post=$i->related_data[0] hide_insight_header=true}
</div>

{if !$expand}
<div class="collapse in" id="chart-{$i->id}">
{/if}
   
    <div id="age_analysis_{$i->id}">&nbsp;</div>
    <script type="text/javascript">
        // Load the Visualization API and the standard charts
        google.load('visualization', '1');
        // Set a callback to run when the Google Visualization API is loaded.
        google.setOnLoadCallback(drawChart{$i->id} );

        {literal}
        function drawChart{/literal}{$i->id}{literal}() {
        {/literal}
            var age_analysis_data_{$i->id} = new google.visualization.arrayToDataTable([
            	['Ages','<18', '18-25', '25-35', '35-45', '>45'],
            	['Number', {$i->related_data[1].18}, {$i->related_data[1].18_25}, {$i->related_data[1].25_35}, {$i->related_data[1].35_45}, {$i->related_data[1].45}]
         	]);
            {literal}
            var age_analysis_chart_{/literal}{$i->id}{literal} = new google.visualization.ChartWrapper({
              containerId: 'age_analysis_{/literal}{$i->id}{literal}',
              chartType: 'ColumnChart',
              dataTable: age_analysis_data_{/literal}{$i->id}{literal},
              'options': {
              		colors: ['#3EA5CF', '#E4BF28', '#5FAC1C', '#DA6070', '#24B98F'],
            		isStacked: false,
                    width: 650,
                    height: 250,
                    chartArea:{left: 100, height:"80%"},
                    legend: { position: 'bottom'},
          		
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
            age_analysis_chart_{/literal}{$i->id}{literal}.draw();
        }
        {/literal}
    </script>
      
{if  !$expand}
</div>
{/if}

{include file=$tpl_path|cat:'_footer.tpl'}