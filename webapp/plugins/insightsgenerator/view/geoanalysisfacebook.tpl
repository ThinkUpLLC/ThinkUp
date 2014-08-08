{include file=$tpl_path|cat:'_header.tpl'}

{if !$expand}
<div class="pull-right detail-btn"><button class="btn btn-info btn-mini" data-toggle="collapse" data-target="#chart-{$i->id}"><i class="icon-signal icon-white"></i></button></div>
{/if}

<span class="label label-{if $i->emphasis eq '1'}info{elseif $i->emphasis eq '2'}success{elseif $i->emphasis eq '3'}error{else}info{/if}"><i class="icon-white icon-globe"></i> <a href="?u={$i->instance->network_username}&n={$i->instance->network}&d={$i->date|date_format:'%Y-%m-%d'}&s={$i->slug}">{$i->headline}</a></span>

<i class="icon-{$i->instance->network}{if $i->instance->network eq 'google+'} icon-google-plus{/if} icon-muted"></i>
{$i->text|link_usernames_to_twitter}

{if !$expand}
<div class="collapse in" id="chart-{$i->id}">
{/if}
   
 <div id="geo_analysis_{$i->id}" style="width: 650px; height: 250px">&nbsp;</div>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
        // Load the Visualization API and the standard charts
        google.load('visualization', '1');
        // Set a callback to run when the Google Visualization API is loaded.
        google.setOnLoadCallback(drawChart{$i->id} );

        {literal}
        function drawChart{/literal}{$i->id}{literal}() {
        {/literal}
            var geo_analysis_data_{$i->id} = new google.visualization.arrayToDataTable([
             	 ['City', 'User']
             	 ]);
             	 {foreach from=$i->related_data[0] item=geo}
             	 geo_analysis_data_{$i->id}.addRows([
             	 ['{$geo.city}','{$geo.name}']
             	 ]);
             	 {/foreach}
            {literal}
            var geo_analysis_chart_{/literal}{$i->id}{literal} = new google.visualization.ChartWrapper({
              containerId: 'geo_analysis_{/literal}{$i->id}{literal}',
              chartType: 'Map',
              dataTable: geo_analysis_data_{/literal}{$i->id}{literal},
              'options': {
            	showTip: true,
            	useMapTypeControl: true
          	}
          	              
            });
            geo_analysis_chart_{/literal}{$i->id}{literal}.draw();
        }
        {/literal}
    </script>
      
{if  !$expand}
</div>
{/if}

{include file=$tpl_path|cat:'_footer.tpl'}