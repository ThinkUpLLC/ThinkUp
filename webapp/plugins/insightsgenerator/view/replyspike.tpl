
<div class="insight-attachment-detail post">
    {include file=$tpl_path|cat:"_post.tpl" post=$i->related_data[0]}
</div>

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
                  colors: ['#6f36b7','#b78ee4', '#ccc'],
                  isStacked: true,
                  width: {/literal}{if $i->emphasis eq '2'}520{else}300{/if}{literal},
                  height: 250,
                  chartArea:{left:100,height:"80%"},
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
